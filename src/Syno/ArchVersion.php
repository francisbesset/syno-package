<?php

namespace Syno;

class ArchVersion
{
    private $package;

    private $arch;

    private $version;

    private $stable;

    private $filePath;

    private $size;

    private $md5;

    private $description;

    private $quickInstall;

    private $depPkgs;

    private $maintainer;

    private $maintainerUrl;

    private $distributor;

    private $distributorUrl;

    private $beta;

    private $downloadCount;

    public function __construct(Package $package, $arch, $version)
    {
        $this->package = $package;
        $this->arch = $arch;
        $this->version = $version;
        $this->downloadCount = 0;
    }

    public static function getArchs()
    {
        return array('noarch', '88f5281', '88f6281', 'armada370', 'armadaxp', 'bromolow', 'cedarview', 'evansport', 'powerpc', 'ppc824x', 'ppc853x', 'ppc854x', 'qoriq', 'x86');
    }

    public function getSlug()
    {
        return $this->package->getSlug();
    }

    public function getArch()
    {
        return $this->arch;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function isStable()
    {
        return $this->stable;
    }

    public function setStable($stable)
    {
        $this->stable = $stable;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function getMd5()
    {
        return $this->md5;
    }

    public function setMd5($md5)
    {
        $this->md5 = $md5;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function isQuickInstall()
    {
        return $this->quickInstall;
    }

    public function setQuickInstall($quickInstall)
    {
        $this->quickInstall = $quickInstall;
    }

    public function getDepPkgs()
    {
        return $this->depPkgs;
    }

    public function setDepPkgs($depPkgs)
    {
        $this->depPkgs = $depPkgs;
    }

    public function getMaintainer()
    {
        return $this->maintainer;
    }

    public function setMaintainer($maintainer)
    {
        $this->maintainer = $maintainer;
    }

    public function getMaintainerUrl()
    {
        return $this->maintainerUrl;
    }

    public function setMaintainerUrl($maintainerUrl)
    {
        $this->maintainerUrl = $maintainerUrl;
    }

    public function getDistributor()
    {
        return $this->distributor;
    }

    public function setDistributor($distributor)
    {
        $this->distributor = $distributor;
    }

    public function getDistributorUrl()
    {
        return $this->distributorUrl;
    }

    public function setDistributorUrl($distributorUrl)
    {
        $this->distributorUrl = $distributorUrl;
    }

    public function isBeta()
    {
        return $this->beta;
    }

    public function setBeta($beta)
    {
        $this->beta = $beta;
    }

    public function getDownloadCount()
    {
        return $this->downloadCount;
    }

    public function setDownloadCount($downloadCount)
    {
        $this->downloadCount = $downloadCount;
    }
}
