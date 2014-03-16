<?php

namespace Syno;

class Package
{
    private $slug;

    private $name;

    private $downloadCount;

    public function __construct()
    {
        $this->downloadCount = 0;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
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
