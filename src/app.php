<?php

use Silex\Application;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use Syno\Extractor;
use Syno\Spk;
use Syno\Storage\PdoStorage;

$app = new Application();
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.encoder.digest' => function () {
        return new PlaintextPasswordEncoder();
    },
    'security.firewalls' => array(
        'admin' => array(
            'pattern' => '^/admin/',
            'http' => true,
            'users' => array(
                'admin' => array('ROLE_ADMIN', 'admin'),
            ),
        ),
    )
));

$app['filesystem'] = $app->share(function ($app) {
    return new Filesystem();
});

$app['finder'] = function ($app) {
    return new Finder();
};

$app['storage'] = $app->share(function ($app) {
    return new PdoStorage(new \PDO($app['db']['dsn'], $app['db']['user'], $app['db']['password']));
});

$app['spk'] = $app->protect(function ($filePath) use ($app) {
    return new Spk(new Extractor($app['filesystem']), $filePath);
});

return $app;
