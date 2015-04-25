<?php

namespace Syno;

use Silex\Application as BaseApplication;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use Syno\Extractor;
use Syno\Spk;
use Syno\Storage\PdoStorage;

class Application extends BaseApplication
{
    public function __construct($env, $debug)
    {
        if ('dev' === $env) {
            $configFile = __DIR__.'/../../config/dev.php.dist';
            if (is_file(__DIR__.'/../../config/dev.php')) {
                $configFile = __DIR__.'/../../config/dev.php';
            }
        } else {
            $configFile = __DIR__.'/../../config/'.$env.'.php';
        }

        parent::__construct(array(
            'env' => $env,
            'debug' => $debug,
        ));

        $config = $this->getConfig(require $configFile);

        $this->register(new \Silex\Provider\UrlGeneratorServiceProvider());
        $this->register(new \Silex\Provider\SecurityServiceProvider(), array(
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
