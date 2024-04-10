<?php

namespace Workbench\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Symfony\Component\Filesystem\Filesystem;
use Workbench\Path;

class WorkbenchPlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @var boolean
     */
    protected $enabled;

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // TODO: Implement deactivate() method.
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // TODO: Implement uninstall() method.
    }

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;

        $this->enabled = !(getenv('WORKBENCH') === '0');

        $this->createWorkbenchConfig();
    }

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::PRE_AUTOLOAD_DUMP => 'registerPackages',
        ];
    }

    protected function createWorkbenchConfig()
    {
        if (!file_exists($workbenchConfigPath = $_SERVER['HOME'] . '/.composer/workbench.json')) {
            $filesystem = new Filesystem();

            $filesystem->dumpFile($workbenchConfigPath, json_encode([
                'targets' => [],
                'sources' => [],
            ], JSON_PRETTY_PRINT));
        }
    }

    public function registerPackages()
    {
        if (!$this->enabled) {
            return;
        }

        if (!file_exists($workbenchConfigPath = $_SERVER['HOME'] . '/.composer/workbench.json')) {
            return;
        }

        $json = json_decode(file_get_contents($workbenchConfigPath));

        // We must have both sources and targets config options
        if (!isset($json->sources) || !isset($json->targets)) {
            return;
        }

        $installedPackages = $this->installedPackages();
        $currentDirectory = getcwd();
        $isEnabled = false;

        foreach ($json->targets as $target) {
            // Check if the current path is part of the target directories
            if (fnmatch($target, $currentDirectory)) {
                $isEnabled = true;
                break;
            }
        }

        if (!$isEnabled) {
            return;
        }

        foreach ($json->sources as $source) {
            $sourcePath = new Path($source, $this->io);

            /*if (!$packages = $source->hasOneOf($installedPackages)) {
                return;
            }*/

            $packages = [];


            foreach ($installedPackages as $installedPackage) {
                if (fnmatch($source, $installedPackage)) {
                    $packages[] = $installedPackage;
                }
            }

            if (!$packages) {
                return;
            }

            foreach ($packages as $package) {
                unset($installedPackages[$package->name]);

                $package->link(
                    $this->composer->getConfig()->get('vendor-dir')
                );
            }
        }
    }

    protected function getPackages()
    {

    }

    protected function installedPackages()
    {
        $packages = [];
        $vendorDir = $this->composer->getConfig()->get('vendor-dir');

        foreach (glob($vendorDir . '/**/*/composer.json') as $package) {
            $json = json_decode(file_get_contents($package));
            $packages[] = $json->name;
        }

        return $packages;
    }
}