<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Syno\ArchVersion;
use Syno\Package;

$console
    ->register('update')
    ->setCode(function(InputInterface $input, OutputInterface $output) use ($console) {
        $app = $console->getApplication();
        var_dump($app['env'], $app['debug']);die;

        $spkDir = __DIR__.'/../spks';
        $finder = $app['finder']
            ->in($spkDir)
            ->files()
            ->name('*.spk')
        ;

        $output->writeln($spkDir);

        $storage = $app['storage'];
        foreach ($finder as $file) {
            $output->writeln(sprintf('Process %s', $file->getRelativePathname()));

            $spk = $app['spk']($file->getPathname());
            $package = $storage->findPackage($spk->getPackage());
            if (!$package) {
                $package = new Package();
                $package->setSlug($spk->getPackage());
                $package->setName($spk->getDisplayName());

                $storage->insertPackage($package);
            }

            $archVersion = $storage->findArchVersion($package, $spk->getArch(), $spk->getVersion());
            if (!$archVersion) {
                $archVersion = new ArchVersion($package, $spk->getArch(), $spk->getVersion());
                $archVersion->setFilePath($file->getRelativePathname());
                $archVersion->setSize($spk->getSize());
                $archVersion->setMd5($spk->getMd5());
                $archVersion->setDescription($spk->getDescription());
                $archVersion->setQuickInstall($spk->isQuickInstall());
                $archVersion->setDepPkgs($spk->getDependencies());
                $archVersion->setMaintainer($spk->getMaintainer());
                $archVersion->setMaintainerUrl($spk->getMaintainerUrl());
                $archVersion->setDistributor($spk->getDistributor());
                $archVersion->setDistributorUrl($spk->getDistributorUrl());
                $archVersion->setBeta($spk->isBeta());

                $storage->insertArchVersion($archVersion);
            }
        }
    })
;
