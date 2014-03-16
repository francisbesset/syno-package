<?php

namespace Syno;

class Spk
{
    const ICON_BASE64 = 0;
    const ICON_BINARY = 1;

    private $extractor;

    private $filePath;

    private $info;

    private $quickInstall;

    public function __construct(Extractor $extractor, $filePath)
    {
        $this->extractor = $extractor;
        $this->setFilePath($filePath);
    }

    public function getPackage()
    {
        $info = $this->getInfo();

        return $info['package'];
    }

    public function getDisplayName()
    {
        $info = $this->getInfo();

        return $info['displayname'];
    }

    public function getArch()
    {
        $info = $this->getInfo();

        return $info['arch'];
    }

    public function getVersion()
    {
        $info = $this->getInfo();

        return $info['version'];
    }

    public function getSize()
    {
        return filesize($this->filePath);
    }

    public function getMd5()
    {
        return md5_file($this->filePath);
    }

    public function getDescription()
    {
        $info = $this->getInfo();

        return $info['description'];
    }

    public function isQuickInstall()
    {
        if (!$this->quickInstall) {
            $this->quickInstall = $this->extractor->extract($this->filePath, 'WIZARD_UIFILES', function ($in) {
                return !is_dir($in.'/WIZARD_UIFILES');
            });
        }

        return $this->quickInstall;
    }

    public function getDependencies()
    {
        $info = $this->getInfo();

        return isset($info['install_dep_packages']) ? $info['install_dep_packages'] : null;
    }

    public function getMaintainer()
    {
        $info = $this->getInfo();

        return $info['maintainer'];
    }

    public function isBeta()
    {
        $info = $this->getInfo();

        return 1 == $info['beta'] ? true : false;
    }

    public function getIcon($format = self::ICON_BASE64)
    {
        $info = $this->getInfo();

        $icon = $info['package_icon'];
        if (self::ICON_BINARY === $format) {
            return base64_decode($icon);
        }

        return $icon;
    }

    private function setFilePath($filePath)
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException(sprintf('The file "%s" is not a regular file or is not readable.', $filePath));
        }

        $this->filePath = $filePath;
    }

    private function getInfo()
    {
        if (!$this->info) {
            $this->info = $this->extractor->extract($this->filePath, 'INFO', function ($in) {
                return parse_ini_file($in.'/INFO');
            });
        }

        return $this->info;
    }
}
