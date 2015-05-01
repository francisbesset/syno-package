<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Syno\ArchVersion;
use Syno\Spk;

$admin = $app['controllers_factory'];

$admin->get('/', function () use ($app) {
    $packages = '';
    $storage = $app['storage'];
    foreach ($storage->findPackages() as $package) {
        $packages .= '<tr><td>'.$package->getName().'</td>';

        $archVersions = array();
        foreach ($storage->findArchVersions($package) as $archVersion) {
            $archVersions[$archVersion->getArch()][] = $archVersion;
        }

        foreach (ArchVersion::getArchs() as $arch) {
            if (!isset($archVersions[$arch])) {
                $packages .= '<td></td>';

                continue;
            }

            $packages .= '<td><select name="packages['.$package->getSlug().']['.$arch.']"><option></option>';
            foreach ($archVersions[$arch] as $archVersion) {
                $packages .= '<option value="'.$archVersion->getVersion().'"'.($archVersion->isStable() ? ' selected="selected"' : '').'>'.$archVersion->getVersion().'</option>';
            }
            $packages .= '</select></td>';
        }

        $packages .= '</tr>';
    }

    $thead = '<th></th>';
    foreach (ArchVersion::getArchs() as $arch) {
        $thead .= '<th>'.$arch.'</th>';
    }

    return new Response(
        '<html><head><title>Synology packages</title></head><body>'.
            '<form method="post" action="'.$app['url_generator']->generate('admin_stables').'">'.
                '<table><thead>'.$thead.'</thead><tbody><tr>'.
                $packages.
                '</tr></tbody></table>'.
                '<input type="submit" value="Stables!" />'.
            '</form>'.
        '</body></html>'
    );
})->bind('admin');

$admin->post('/stables', function (Request $request) use ($app) {
    foreach ($request->request->get('packages', []) as $slug => $archs) {
        $package = $app['storage']->findPackage($slug);

        if (!$package) {
            continue;
        }

        foreach ($archs as $arch => $version) {
            $archVersionStable = $app['storage']->findArchVersionStable($package, $arch);
            if (!$archVersionStable) {
                if (empty($version)) {
                    continue;
                }
            } elseif ($archVersionStable->getVersion() === $version) {
                continue;
            }

            if (!empty($version)) {
                $archVersion = $app['storage']->findArchVersion($package, $arch, $version);
                if (!$archVersion) {
                    continue;
                }
            }

            if ($archVersionStable) {
                $archVersionStable->setStable(false);
                $app['storage']->updateArchVersion($archVersionStable);
            }

            if ($archVersion) {
                file_put_contents(
                    __DIR__.'/../web/thumb/'.$package->getSlug().'.png',
                    $app['spk'](__DIR__.'/../spks/'.$archVersion->getFilePath())->getIcon(Spk::ICON_BINARY)
                );

                $archVersion->setStable(true);
                $app['storage']->updateArchVersion($archVersion);
            }
        }
    }

    return $app->redirect($app['url_generator']->generate('admin'));
})->bind('admin_stables');

return $admin;
