# Composer Workbench

A workbench for Composer that allows you to symlink your "offline" packages without writing them to the `composer.lock` file.

## Installation

Workbench is a plugin for Composer and installs like so:

`composer global require tormjens/workbench`

## Usage

Once installed you'll have to create a `workbench.json` file in your root composer directory. On a mac that's usually `/Users/username/.composer`. 

The file should look like this:
```json
{
  "paths": [
  ]
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

