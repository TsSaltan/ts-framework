# PHP Framework
[https://github.com/TsSaltan/ts-framework/wiki](Wiki)

## Install
### Composer
```json
{
    "name": "...",
    "description": "...",
    "type": "application",
    "authors": [
        {
            "name": "...",
            "email": "..."
        }
    ],

    "repositories": [
        {
            "type":"git",
            "url":"https://github.com/TsSaltan/ts-framework"
        },        

        {
            "type":"git",
            "url":"https://github.com/TsSaltan/ts-framework-composer"
        }
    ],

    "require": {
        "php": ">=7.1",
        "tssaltan/ts-framework": "dev-3.0-stable",
        "tssaltan/ts-framework-composer": "dev-master"
    },


    "scripts": {
        "post-install-cmd": [
            "tsframe\\Installer::installFramework"
        ],
        "post-update-cmd": [
            "tsframe\\Installer::installFramework"
        ]
    }
```

```bash
composer install
```

### .gitignore file example
```
# Ignore all framework files
/~dir/*.*
/~dir/storage/*
/~dir/ts-framework/*
/~dir/ts-plugins/*
/~dir/ts-template/*
/~dir/vendor/*

# Instead of your project files 
!/~dir/composer.json
!/~dir/ts-plugins/my-plugin-name/
```