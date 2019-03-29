<?php

namespace Workbench;

use Composer\Util\Filesystem as ComposerFilesystem;
use Symfony\Component\Filesystem\Filesystem;
use Composer\IO\IOInterface;

class Package
{
    public $name;

    protected $configFile;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @var ComposerFilesystem\
     */
    protected $filesystem;

    public function __construct(array $package, string $configFile, IOInterface $io)
    {
        $this->name = $package['name'] ?? null;
        $this->configFile = $configFile;
        $this->io = $io;
        $this->filesystem = new ComposerFilesystem();
    }

    public function getDirectory()
    {
        return realpath(str_replace('composer.json', '', $this->configFile));
    }

    public function link($vendorDir)
    {
        $fileSystem = new Filesystem();

        if (file_exists($targetDir = $vendorDir . '/' . $this->name)) {
            $sourceDir = $this->getDirectory();

            $this->io->writeError(sprintf("\n" . '[WORKBENCH] Symlinking "%s" to "%s".' . "\n", $this->name, $sourceDir), false);

            $fileSystem->remove($targetDir);
            $fileSystem->symlink($sourceDir, $targetDir);
        }

    }

}