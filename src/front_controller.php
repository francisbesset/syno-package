<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

$front = $app['controllers_factory'];

$front->get('/', function() use ($app) {
    $packages = '';
    foreach ($app['storage']->findPackages() as $package) {
        $packages .= '<li>'.$package->getName().'</li>';
    }

    return new Response(
        '<!DOCTYPE html>'.
        '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">'.
            '<head>'.
                '<meta charset="utf-8">'.
                '<title>Synology Packages</title>'.
            '</head>'.
            '<body>'.
                '<h1>Welcome!</h1>'.
                '<p>You can use this repository on your NAS Synology for free.</p>'.

                '<h2>Packages available</h2>'.
                '<ul>'.$packages.'</ul>'.

                '<h2>How to install?</h2>'.
                '<p>Following steps explain the process to add this repository:'.
                //'But if you need some help you can refer to the help on <a href="http://www.synology.com/en-us/support/tutorials/500#t2.2">Synology website</a>.</p>'.
                '<ol>'.
                    '<li>Launch "Package Center" application</li>'.
                    '<li>Click on "Settings" button</li>'.
                    '<li>In the popin, click on "Add" button</li>'.
                    '<li>Specify a name</li>'.
                    '<li>Add location "'.$app['url_generator']->generate('homepage', array(), true).'" (without double quotes)</li>'.
                    '<li>Click on "Ok" button and enjoy it!</li>'.
                '</ol>'.

                '<h2>How can I thank you?</h2>'.
                '<p>I love money! You can send me a lot money by this PayPal button: '.
                    '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=MTE9TTQYMC79Y">'.
                        '<img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" alt="PayPal - The safer, easier way to pay online!" />'.
                    '</a>'.
                '</p>'.
            '</body>'.
        '</html>'
    );
})->bind('homepage');

$front->post('/', function(Request $request) use ($app) {
    $packages = array();
    $arch = $request->request->get('arch');
    $storage = $app['storage'];
    foreach ($storage->findPackages() as $package) {
        $archVersion = $storage->findArchVersionStable($package, $arch);
        if (!$archVersion) {
            continue;
        }

        $packages[] = array(
            'package' => $package->getSlug(),
            'version' => $archVersion->getVersion(),
            'dname' => $package->getName(),
            'desc' => $archVersion->getDescription(),
            'link' => $app['url_generator']->generate('download', array(
                'slug' => $package->getSlug(),
                'arch' => $archVersion->getArch(),
                'version' => $archVersion->getVersion(),
            ), $app['url_generator']::ABSOLUTE_URL),
            'md5' => $archVersion->getMd5(),
            'icon' => base64_encode(file_get_contents(__DIR__.'/../web/thumb/'.$package->getSlug().'.png')),
            'size' => $archVersion->getSize(),
            'qinst' => !$archVersion->isQuickInstall(),
            'depsers' => null,
            'deppkgs' => $archVersion->getDepPkgs(),
            'start' => true,
            'maintainer' => $archVersion->getMaintainer(),
            'changelog' => null,
            'beta' => $archVersion->isBeta(),
            // 'download_count' => $package->getDownloadCount(),
        );
    }

    return $app->json($packages);
});

$front->get('/download/{slug}/{arch}/{version}', function ($slug, $arch, $version) use ($app) {
    $package = $app['storage']->findPackage($slug);
    if (!$package) {
        $app->abort(404, sprintf('Package "%s" does not exists.', $slug));
    }

    $archVersion = $app['storage']->findArchVersion($package, $arch, $version);
    if (!$archVersion) {
        $app->abort(404, sprintf('"%s" does not exists.', $slug));
    }

    $app['storage']->increment($archVersion);

    return $app
        ->sendFile(__DIR__.'/../spks/'.$archVersion->getFilePath())
        ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $slug.'-'.$arch.'-'.$version.'.spk')
    ;
})->bind('download');

return $front;
