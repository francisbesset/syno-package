<?php

namespace Syno;

use Monolog\Logger;
use Monolog\Handler\NullHandler;
use Silex\Application as BaseApplication;
use Silex\Provider;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use Syno\Extractor;
use Syno\Monolog\Processor;
use Syno\Spk;
use Syno\Storage\PdoStorage;

class Application extends BaseApplication
{
    const VERSION = '1.0.0-DEV';

    public function __construct($env, $debug)
    {
        parent::__construct(array(
            'env' => $env,
            'debug' => $debug,
        ));

        if ('dev' === $env) {
            $configFile = __DIR__.'/../../config/dev.php.dist';
            if (is_file(__DIR__.'/../../config/dev.php')) {
                $configFile = __DIR__.'/../../config/dev.php';
            }
        } else {
            $configFile = __DIR__.'/../../config/'.$env.'.php';
        }

        $config = $this->getConfig(require $configFile);

        $this->register(new Provider\UrlGeneratorServiceProvider());
        $this->register(new Provider\SecurityServiceProvider(), array(
            'security.encoder.digest' => function () {
                return new PlaintextPasswordEncoder();
            },
            'security.firewalls' => array(
                'admin' => array(
                    'pattern' => '^/admin/',
                    'http' => true,
                    'users' => $config['admin.users'],
                ),
            )
        ));

        $this->register(new Provider\MonologServiceProvider(), array(
            'monolog.level' => $config['monolog.level'],
            'monolog.handler' => $config['monolog.handler'],
        ));

        $this['monolog.processor.application'] = $this->share(function () {
            $processor =  new Processor\ApplicationProcessor($this);

            $this->before(function (Request $request) use ($processor) {
                $processor->setRequest($request);
            });

            return $processor;
        });

        $this['monolog.processor.web'] = $this->share(function() {
            $processor =  (new Processor\WebProcessor([]))
                ->addExtraField('ip_real', 'HTTP_X_REAL_IP')
            ;

            $this->before(function (Request $request) use ($processor) {
                $processor->setRequest($request);
            });

            return $processor;
        });

        $this['monolog.processor.synology'] = $this->share(function() {
            $processor = new Processor\SynologyProcessor();

            $this->before(function (Request $request) use ($processor) {
                $processor->setRequest($request);
            });

            return $processor;
        });

        $this['monolog.syno_package.level'] = $config['monolog.syno_package.level'];
        $this['monolog.syno_package.handler'] = $config['monolog.syno_package.handler'];
        $this['monolog.syno_package'] = $this->share(function () {
            $log = new $this['monolog.logger.class']('syno_package');

            $handlers = $this['monolog.syno_package.handler'];
            if (!is_array($handlers)) {
                $handlers = array($handlers);
            }

            foreach ($handlers as $handler) {
                $log->pushHandler($handler);
            }

            return $log;
        });

        $this['filesystem'] = $this->share(function () {
            return new Filesystem();
        });

        $this['finder'] = function () {
            return new Finder();
        };

        $this['storage'] = $this->share(function () use ($config) {
            return new PdoStorage(new \PDO($config['db.dsn'], $config['db.user'], $config['db.password']));
        });

        $this['spk'] = $this->protect(function ($filePath) {
            return new Spk(new Extractor($this['filesystem']), $filePath);
        });
    }

    private function getConfig(array $config)
    {
        $resolver = new OptionsResolver();

        return $resolver->setDefaults(array(
            'admin.users' => array('admin' => 'admin'),

            'monolog.level' => Logger::NOTICE,
            'monolog.handler' => function () {
                return new NullHandler($this['monolog.level']);
            },

            'monolog.syno_package.level' => Logger::INFO,
            'monolog.syno_package.handler' => function () {
                return new NullHandler($this['monolog.syno_package.level']);
            },
        ))->setRequired(array(
            'db.dsn', 'db.user', 'db.password',
        ))->setNormalizer('admin.users', function (OptionsResolver $options, $users) {
            foreach ($users as $user => $password) {
                $users[$user] = array('ROLE_ADMIN', $password);
            }

            return $users;
        })->resolve($config);
    }
}
