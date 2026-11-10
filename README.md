# MediaArea-Website

![Build Status](https://github.com/MediaArea/MediaArea-Website/workflows/Checks/badge.svg)

# How to install

## Dependencies

* A web server (tested on Apache 2.4)
    * mod_rewrite is recommended
* PHP >= 8.4 (tested on version 8.5)
    * Mandatory packages for Debian-like distributions : `sudo apt-get install libapache2-mod-php8.5 php8.5-cli php8.5-intl php8.5-curl php8.5-mysql php8.5-mbstring php8.5-xml php8.5-gd`
    * Mandatory packages for RedHat-like distributions : `sudo dnf install php php-cli php-intl php-mbstring php-pdo php-process php-xml php-gd php-mysqlnd`
    * date.timezone parameter should be set in your php.ini (both cli and apache module)
* MySQL >= 8.0 or MariaDB >= 10.6 (tested on MySQL 9.7 and MariaDB 10.6)
* [Composer](https://getcomposer.org/download/) 2

## Get sourcecode

### From git

Clone MediaArea-Website repository
```
git clone https://github.com/MediaArea/MediaArea-Website.git
```
## Install

### MySQL

Create a user and a datable, give the user the all rights to this database.
```
CREATE USER '<USER>'@'<HOST>' IDENTIFIED BY '<PASSWORD>';
GRANT ALL ON `<DB>`.* TO '<USER>'@'<HOST>';
CREATE DATABASE IF NOT EXISTS `<DB>`;
```

### Dependencies

Get dependencies with composer
```
cd YOUR_PATH/MediaArea-Website/
composer install
```
You'll get some configuration questions, you can use [these fake values](blob/master/app/config/parameters.yml.ci) if you don't care about the payments.

### SQL tables and assets

```
php bin/console doctrine:migrations:migrate --env=prod --no-interaction
php bin/console asset-map:compile --env=prod
```

`asset-map:compile` writes the stylesheets, scripts and images of the bundles in `web/assets`, run it again after a modification of one of them.  
When `web/assets` does not exist, e.g. for development, these files are served directly from the bundles (with `app_dev.php`).

### Update from a version using Symfony 3.4

The migrations are stored with their class name, the table of the executed migrations has to be updated once before the migration of the database:
```
php bin/console doctrine:migrations:sync-metadata-storage --env=prod
php bin/console doctrine:migrations:migrate --env=prod --no-interaction
php bin/console asset-map:compile --env=prod
```
The files of the previous version in `var/cache` and `var/sessions` have to be removed (users have to log in again), the directories `web/css`, `web/js` and `web/images` are not used any more.

### Apache

Add a vhost to access MediaArea-Website, like this minimal example :
```
<VirtualHost *:80>
    ServerName WWW.YOURWEBSITE.COM
    DocumentRoot "YOUR_PATH/MediaArea-Website/web/"
    <Directory "YOUR_PATH/MediaArea-Website/web/">
        AllowOverride All
        Options -Indexes
        <IfModule mod_authz_core.c>
          # Apache 2.4
            Require all granted
        </IfModule>
        <IfModule !mod_authz_core.c>
          # Apache 2.2
            Order allow,deny
            allow from all
        </IfModule>
    </Directory>
</VirtualHost>

```
Allow apache user to write in cache and log directory, some methods are explain in [Symfony documentation](https://symfony.com/doc/current/setup/file_permissions.html)

# Doc

## Translations

Translations are stored is XML format [here](https://github.com/MediaArea/MediaArea-Website/blob/master/app/Resources/translations/) one file per language

## Template

Templates are in twig format, they are available [here](https://github.com/MediaArea/MediaArea-Website/tree/master/src/AppBundle/Resources/views)

## Coding standard

MediaArea-Website follow [PSR1](http://www.php-fig.org/psr/psr-1/) and [PSR2](http://www.php-fig.org/psr/psr-2/) standard  
The source code is analyzed with [PHP Mess Detector](https://phpmd.org/) with the rules in [phpmd.xml](https://github.com/MediaArea/MediaArea-Website/blob/master/phpmd.xml) and [PHP-CS-Fixer](https://cs.symfony.com/) with PSR1 and PSR2 rules, see [.php-cs-fixer.dist.php](https://github.com/MediaArea/MediaArea-Website/blob/master/.php-cs-fixer.dist.php)

## Tests

The tests need a database with the fixtures and the users created by the [checks](https://github.com/MediaArea/MediaArea-Website/blob/master/.github/workflows/MediaArea-Website_Checks.yml), then:
```
vendor/bin/phpunit
```

## Cache

Remind to clear the cache after a modification :
```
php bin/console cache:clear --env=prod
```
