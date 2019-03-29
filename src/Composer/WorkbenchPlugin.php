<?php

namespace Workbench\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
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

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::PRE_AUTOLOAD_DUMP => 'registerWorkbenchPackages',
        ];
    }

    public function registerWorkbenchPackages()
    {
        if (file_exists($workbenchConfigPath = $_SERVER['HOME'] . '/.composer/workbench.json')) {
            $installedPackages = $this->installedPackages();
            $json = json_decode(file_get_contents($workbenchConfigPath));
            if (isset($json->paths)) {
                foreach ($json->paths as $path) {
                    $path = new Path($path, $this->io);

                    if ($packages = $path->hasOneOf($installedPackages)) {
                        foreach ($packages as $package) {
                            unset($installedPackages[$package->name]);
                            $package->link($this->composer->getConfig()->get('vendor-dir'));
                        }
                    }
                }
            }
        }
    }

    protected function installedPackages()
    {
        $packages = [];

        foreach (glob($this->composer->getConfig()->get('vendor-dir') . '/**/*/composer.json') as $package) {
            $json = json_decode(file_get_contents($package));
            $packages[] = $json->name;
        }

        return $packages;
    }

}