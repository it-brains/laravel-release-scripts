# Release Script

This package allows creating one-off execute scripts. This should be useful when after some changes need run additional commands (for example, need add a new role to app when we implemented a new feature).

## Install

Require this package with composer using the following command:

```
composer require it-brains/laravel-release-scripts
```

## How use

Step 1. Create a new release script:
    
```
php artisan make:release-script add_manage_role
```

    On './database/scripts' path will be create a class in migration format with 'run' method. The file can look like:
    
```php
<?php

use ITBrains\ReleaseScript\ScriptInterface;

class AddManagerRole implements ScriptInterface
{
    /**
     * Run the script.
     *
     * @return void
     */
    public function run()
    {
        \App\Role::create(['title' => 'Manager']);
    }
}
```

Step 2. To edit on your server deploy script - replace migrate command (php artisan migrate --force) with release scripts run command:
    
```
...
php artisan release-script:run --force --migrate
...
```

Note: with '--migrate' option the 'migrate' command running before all release scripts.

## Available commands
```
php artisan make:release-script 
php artisan release-script:run
php artisan release-script:status
```

## Possible todo:
* tests
* add fresh command that will remove all scripts from database and run again
* possible need move creating 'scripts' table to migration because how we will know that need reload all scripts when a developer ran 'php artisan migrate:refresh --seed' - all will be refreshed except scripts! And then really possible need extend migrations command for add the option '--scripts'.
