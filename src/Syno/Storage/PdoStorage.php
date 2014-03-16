<?php

namespace Syno\Storage;

use Syno\ArchVersion;
use Syno\Package;

class PdoStorage
{
    private $db;

    public function __construct(\PDO $con)
    {
        $this->db = $con;
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function findPackages()
    {
        $stmt = $this->db->prepare('SELECT slug, name, download_count FROM package');

        if (!$stmt->execute()) {
            throw new \RuntimeException('Unable to retrieve packages');
        }

        $packages = array();
        while ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $packages[] = $this->createPackage($result);
        }

        return $packages;
    }

    public function findPackage($slug)
    {
        $stmt = $this->db->prepare('SELECT slug, name, download_count FROM package WHERE slug = :slug');
        $stmt->bindValue(':slug', $slug, \PDO::PARAM_STR);

        if (!$stmt->execute()) {
            throw new \RuntimeException(sprintf('Unable to retrieve package "%s"', $name));
        } elseif (false === $result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return null;
        }

        return $this->createPackage($result);
    }

    public function insertPackage(Package $package)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO package (slug, name, download_count) VALUES(:slug, :name, :download_count)'
        );
        $stmt->bindValue(':slug', $package->getSlug(), \PDO::PARAM_STR);
        $stmt->bindValue(':name', $package->getName(), \PDO::PARAM_STR);
        $stmt->bindValue(':download_count', $package->getDownloadCount(), \PDO::PARAM_INT);

        if (!$stmt->execute()) {
            throw new \RuntimeException(sprintf('Unable to create package %s', $package->getName));
        }
    }

    public function findArchVersions(Package $package)
    {
        $stmt = $this->db->prepare(
            'SELECT arch, version, file_path, stable, size, md5, description, qinst, deppkgs, maintainer, beta, download_count FROM arch_version WHERE slug = :slug'
        );
        $stmt->bindValue(':slug', $package->getSlug(), \PDO::PARAM_STR);

        if (!$stmt->execute()) {
            throw new \RuntimeException(sprintf('Unable to retrieve arch versions package "%s"', $package->getName()));
        }

        $archVersions = array();
        while ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $archVersions[] = $this->createArchVersion($package, $result);
        }

        return $archVersions;
    }

    public function findArchVersionStable(Package $package, $arch)
    {
        $stmt = $this->db->prepare(
            'SELECT arch, version, file_path, stable, size, md5, description, qinst, deppkgs, maintainer, beta, download_count FROM arch_version WHERE slug = :slug AND arch = :arch AND stable = :stable'
        );
        $stmt->bindValue(':slug', $package->getSlug(), \PDO::PARAM_STR);
        $stmt->bindValue(':arch', $arch, \PDO::PARAM_STR);
        $stmt->bindValue(':stable', true, \PDO::PARAM_BOOL);

        if (!$stmt->execute()) {
            throw new \RuntimeException(sprintf('Unable to retrieve the stable version of "%s" for "%s"', $package->getSlug(), $arch));
        } elseif (false === $result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ('noarch' === $arch) {
                return null;
            }

            // Fallback with noarch
            return $this->findArchVersionStable($package, 'noarch');
        }

        return $this->createArchVersion($package, $result);
    }

    public function findArchVersion(Package $package, $arch, $version)
    {
        $stmt = $this->db->prepare(
            'SELECT arch, version, file_path, stable, size, md5, description, qinst, deppkgs, maintainer, beta, download_count FROM arch_version WHERE slug = :slug AND arch = :arch AND version = :version'
        );
        $stmt->bindValue(':slug', $package->getSlug(), \PDO::PARAM_STR);
        $stmt->bindValue(':arch', $arch, \PDO::PARAM_STR);
        $stmt->bindValue(':version', $version, \PDO::PARAM_STR);

        if (!$stmt->execute()) {
            throw new \RuntimeException(sprintf('Unable to retrieve arch "%s" for package "%s"', $arch, $package->getName()));
        } elseif (false === $result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return null;
        }

        return $this->createArchVersion($package, $result);
    }

    public function updateArchVersion(ArchVersion $archVersion)
    {
        $stmt = $this->db->prepare(
            'UPDATE arch_version SET stable = :stable WHERE slug = :slug AND arch = :arch AND version = :version'
        );
        $stmt->bindValue(':stable', $archVersion->isStable(), \PDO::PARAM_BOOL);
        $stmt->bindValue(':slug', $archVersion->getSlug(), \PDO::PARAM_STR);
        $stmt->bindValue(':arch', $archVersion->getArch(), \PDO::PARAM_STR);
        $stmt->bindValue(':version', $archVersion->getVersion(), \PDO::PARAM_STR);

        if (!$stmt->execute()) {
            throw new \RuntimeException();
        }
    }

    public function insertArchVersion(ArchVersion $archVersion)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO arch_version (slug, arch, version, file_path, size, md5, description, qinst, deppkgs, maintainer, beta, download_count) '.
            'VALUES(:slug, :arch, :version, :file_path, :size, :md5, :description, :qinst, :deppkgs, :maintainer, :beta, :download_count)'
        );
        $stmt->bindValue(':slug', $archVersion->getSlug(), \PDO::PARAM_STR);
        $stmt->bindValue(':arch', $archVersion->getArch(), \PDO::PARAM_STR);
        $stmt->bindValue(':version', $archVersion->getVersion(), \PDO::PARAM_STR);
        $stmt->bindValue(':file_path', $archVersion->getFilePath(), \PDO::PARAM_STR);
        $stmt->bindValue(':size', $archVersion->getSize(), \PDO::PARAM_STR);
        $stmt->bindValue(':md5', $archVersion->getMd5(), \PDO::PARAM_STR);
        $stmt->bindValue(':description', $archVersion->getDescription(), \PDO::PARAM_STR);
        $stmt->bindValue(':qinst', $archVersion->isQuickInstall(), \PDO::PARAM_BOOL);
        $stmt->bindValue(':deppkgs', $archVersion->getDepPkgs(), \PDO::PARAM_STR);
        $stmt->bindValue(':maintainer', $archVersion->getMaintainer(), \PDO::PARAM_STR);
        $stmt->bindValue(':beta', $archVersion->isBeta(), \PDO::PARAM_BOOL);
        $stmt->bindValue(':download_count', $archVersion->getDownloadCount(), \PDO::PARAM_INT);

        if (!$stmt->execute()) {
            throw new \RuntimeException();
        }
    }

    public function increment(ArchVersion $archVersion)
    {
        $stmt = $this->db->prepare(
            'UPDATE package SET download_count = download_count + 1 WHERE slug = :slug'
        );
        $stmt->bindValue(':slug', $archVersion->getSlug(), \PDO::PARAM_STR);

        if (!$stmt->execute()) {
            throw new \RuntimeException();
        }

        $stmt = $this->db->prepare(
            'UPDATE arch_version SET download_count = download_count + 1 WHERE slug = :slug AND arch = :arch AND version = :version'
        );
        $stmt->bindValue(':slug', $archVersion->getSlug(), \PDO::PARAM_STR);
        $stmt->bindValue(':arch', $archVersion->getArch(), \PDO::PARAM_STR);
        $stmt->bindValue(':version', $archVersion->getVersion(), \PDO::PARAM_STR);

        if (!$stmt->execute()) {
            throw new \RuntimeException();
        }
    }

    private function createPackage(array $result)
    {
        $package = new Package();
        $package->setSlug($result['slug']);
        $package->setName($result['name']);
        $package->setDownloadCount($result['download_count']);

        return $package;
    }

    private function createArchVersion(Package $package, array $result)
    {
        $archVersion = new ArchVersion($package, $result['arch'], $result['version']);
        $archVersion->setStable(1 == $result['stable'] ? true : false);
        $archVersion->setFilePath($result['file_path']);
        $archVersion->setSize($result['size']);
        $archVersion->setMd5($result['md5']);
        $archVersion->setDescription($result['description']);
        $archVersion->setQuickInstall(1 == $result['qinst'] ? true : false);
        $archVersion->setDepPkgs($result['deppkgs']);
        $archVersion->setMaintainer($result['maintainer']);
        $archVersion->setBeta(1 == $result['beta'] ? true : false);
        $archVersion->setDownloadCount($result['download_count']);

        return $archVersion;
    }
}
