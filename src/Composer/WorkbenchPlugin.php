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
            ScriptEvents::POST_INSTALL_CMD => 'registerWorkbenchPackages',
            ScriptEvents::POST_UPDATE_CMD => 'registerWorkbenchPackages',
        ];
    }

    public function registerWorkbenchPackages()
    {
        if (file_exists($workbenchConfigPath = $_SERVER['HOME'] . '/.composer/workbench.json')) {
            $installedPackages = array_keys($this->composer->getPackage()->getRequires());
            $json = json_decode(file_get_contents($workbenchConfigPath));

            $paths = [];
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

}