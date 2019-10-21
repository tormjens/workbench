# Composer Workbench

A workbench for Composer that allows you to symlink your "offline" packages without writing them to the `composer.lock` file.

__!!Please note!!__ This package has only been tested on OS X. It should work fine for Linux, but probably not for Windows.

## Installation

Workbench is a plugin for Composer and installs like so:

`composer global require tormjens/workbench`

## Usage

Once installed you'll notice a `workbench.json` file in your root composer directory (create it if you can't find it). On a mac that's usually `/Users/username/.composer`. 

The file should look like this:
```json
{
  "paths": []
}
```

Inside the `paths` key you'll place the absolute paths to where your local packages are located. It will search using a glob so if you have many packages you may specify the "top level".

For example if you have packages at

* `/Users/username/packages/myfirstpackage`
* `/Users/username/packages/mysecondpackage`
* `/Users/username/packages/mythirdpackage`

You would then only add the path `/Users/username/packages` and all of your packages would be found.

So your `workbench.json` would look like:

```json
{
  "paths": [
    "/Users/username/packages"
  ]
}
```

### Run Composer without Workbench

Some times, for various reasons, you may want to run `composer install` and other commands without triggering workbench. 
In that case you may prefix the command with `WORKBENCH=0`. This will deactivate Workbench for that run.

`WORKBENCH=0 composer install`


## Caveats

While Workbench solves the issue of your custom local packages not being written to your project's `.lock` file, it will
not be able to detect local changes to your `composer.json` during install. Hence you'll need to push and update for every
of those changes.
