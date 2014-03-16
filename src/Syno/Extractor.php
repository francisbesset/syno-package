<?php

namespace Syno;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ProcessBuilder;

class Extractor
{
    private $fs;

    public function __construct(Filesystem $fs)
    {
        $this->fs = $fs;
    }

    public function extract($archive, $filesToExtract = array(), Callable $callable)
    {
        $tempDir = $this->getTempDir();

        $pb = ProcessBuilder::create(array(
            'tar', '-C', $tempDir, '-xf', $archive
        ));

        foreach ((array) $filesToExtract as $file) {
            $pb->add($file);
        }

        $process = $pb->getProcess()->run();

        $returnValue = $callable($tempDir);
        $this->remove($tempDir);

        return $returnValue;
    }

    private function getTempDir()
    {
        $tempDir = sys_get_temp_dir().'/syno_package'.rand(1000,9999);

        if ($this->fs->exists($tempDir)) {
            return $this->getTempDir();
        }

        $this->fs->mkdir($tempDir);

        return $tempDir;
    }

    private function remove($file)
    {
        $this->fs->remove($file);
    }
}
