<?php

namespace Workbench;

use Composer\Json\JsonFile;
use Composer\IO\IOInterface;

class Path
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @var array
     */
    protected $packages = [];

    public function __construct(string $directory, IOInterface $io)
    {
        $this->directory = $directory;
        $this->io = $io;

        $this->findPackages();
    }

    public function findPackages()
    {
        $composerConfigs = glob($this->directory . '/**/*composer.json');

        if ($composerConfigs) {
            foreach ($composerConfigs as $configFile) {
                $json = file_get_contents($configFile);
                $package = JsonFile::parseJson($json, $configFile);
                $this->packages[$package['name']] = new Package($package, $configFile, $this->io);
            }
        }
    }

    public function hasOneOf(array $packages)
    {
        $foundPackages = array_intersect($packages, array_keys($this->packages));

        if ($foundPackages) {
            return array_map(function ($package) {
                return $this->packages[$package];
            }, $foundPackages);
        }

        return false;
    }
}