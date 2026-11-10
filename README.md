# MediaArea-Website

![Build Status](https://github.com/MediaArea/MediaArea-Website/workflows/Checks/badge.svg)

# How to install

## Dependencies

* A web server (tested on Apache 2.4)
    * mod_rewrite is recommended
* PHP >= 8.1 (tested on versions 8.1 to 8.5), the version of Symfony depends on it, see [Supported versions](#supported-versions)
    * Mandatory packages for Debian-like distributions : `sudo apt-get install libapache2-mod-php8.5 php8.5-cli php8.5-intl php8.5-curl php8.5-mysql php8.5-mbstring php8.5-xml php8.5-gd`
    * Mandatory packages for RedHat-like distributions : `sudo dnf install php php-cli php-intl php-mbstring php-pdo php-process php-xml php-gd php-mysqlnd`
    * date.timezone parameter should be set in your php.ini (both cli and apache module)
* MySQL >= 5.6 or MariaDB >= 10.2 (tested on MySQL 5.6 to 9.7, MariaDB 10.2 to 13.0 and Percona XtraDB Cluster 5.7)
* [Composer](https://getcomposer.org/download/) 2

## Supported versions

The website is not tied to one version of Symfony: the same code works with Symfony 6.4, 7 and 8. Each version of Symfony needs a minimal version of PHP, and Composer installs the latest one which supports the PHP of the server. So PHP 8.1 is supported with Symfony 6.4, not with Symfony 8, which needs PHP 8.4.

| PHP      | Symfony | Dependencies installed with |
|----------|---------|-----------------------------|
| 8.1      | 6.4     | `composer update`           |
| 8.2, 8.3 | 7.4     | `composer update`           |
| 8.4, 8.5 | 8       | `composer install`          |

`composer install` installs the exact versions listed in `composer.lock`, and this file can list only one version of each dependency: it has the current ones, which need PHP 8.4. With PHP 8.1 to 8.3 Composer refuses to install them, so `composer update` is used instead: it ignores `composer.lock` and selects the latest versions which support the installed version of PHP.

Other versions of the main dependencies can be used too:

| Dependency    | Supported versions  |
|---------------|---------------------|
| Symfony       | 6.4, 7.2 to 7.4, 8  |
| Doctrine ORM  | 2.20, 3.4 and later |
| Doctrine DBAL | 3.8 and later, 4    |

A version which is not the one Composer selects is requested with the `--with` option, e.g. `composer update --with doctrine/orm:^2.20` for Doctrine ORM 2 or `composer update --with doctrine/dbal:^3.8.2` for Doctrine DBAL 3.

The lines of the first table, Doctrine ORM 2 and Doctrine DBAL 3 are tested by the [checks](https://github.com/MediaArea/MediaArea-Website/blob/master/.github/workflows/MediaArea-Website_Checks.yml), with several versions of MySQL and MariaDB and with Percona XtraDB Cluster.

Why these limits:

* PHP 8.1 is the minimum because Symfony 6.4 requires it, and Symfony 6.4 is the oldest version of Symfony with the AssetMapper component and the attributes used by the controllers (`Route`, `Template`, `IsGranted`, `MapEntity`).
* Symfony 7.0 and 7.1 are not supported: DoctrineFixturesBundle 4.3, used by the tests, can not be installed with Symfony 7.0, and with Symfony 7.1 the country and the language of the user are not selected in the form of the profile, which is corrected in Symfony 6.4.20 and 7.2.5 only.
* Symfony 7.2, 7.3 and 8.0 are supported but, as 7.0 and 7.1, they are not maintained any more: Composer refuses by default to install them because of their known security issues.

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
You'll get some configuration questions, you can use [these fake values](blob/master/app/config/parameters.yml.ci) if you don't care about the payments, or [simulate them](#payments).

With PHP 8.1, 8.2 or 8.3, run `composer update` instead of `composer install`, see [Supported versions](#supported-versions).

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

## User accounts

The user accounts are handled by UserBundle, whose code is derived from FOSUserBundle (it has no version for Symfony 8), with two maintained bundles:

* [SymfonyCastsResetPasswordBundle](https://github.com/SymfonyCasts/reset-password-bundle) for the reset of the password: the link of the email is valid for one day, and a new email can be requested after two hours (`user.resetting.*` parameters of `app/config/config.yml`);
* [SymfonyCastsVerifyEmailBundle](https://github.com/SymfonyCasts/verify-email-bundle) for the link which confirms the email address of a new user. This confirmation is disabled (`user.registration.confirmation.enabled` parameter). When it is enabled, the link is signed for its whole URL: the website has to see the scheme and the host that the user sees, so the proxies in front of it have to be declared as trusted.

## Payments

The payments are made with Paypal Express Checkout, through the gateway of [Payum](https://github.com/Payum/Payum) for the NVP API of Paypal: its credentials are the `paypal_username`, `paypal_password` and `paypal_signature` parameters.

Real payments can be tested without money with the sandbox of Paypal, e.g. on a test website: set `paypal_sandbox: true` in `app/config/parameters.yml` (the development environment always uses the sandbox) with the NVP API credentials of a sandbox "Business" account, created on the [developer website of Paypal](https://developer.paypal.com/), clear the cache, then pay with a sandbox "Personal" account. Paypal only sends the browser of the user back to the website, which does not have to be reachable from the Internet.

The payments can be simulated, to test them without Paypal and without credentials: set `payment_simulation: true` in `app/config/parameters.yml`, then clear the cache. Nothing is sent to Paypal and no money is involved: the user is sent to a page of the website instead of the one of Paypal, where he chooses what happens to the payment:

* the payment is made;
* the user gives up;
* Paypal declines the payment (error 10486);
* Paypal holds the payment for review (status `Pending`).

The website then does what it does with Paypal (order, invoice, donation of the user), as only the answers of the API of Paypal are simulated. The transactions of the simulated payments are named `SIMULATED-...`.  
Never simulate the payments on a public website: everybody would get what a payment gives without paying.

The tests always simulate the payments, the ones of [PaymentFlowTest](https://github.com/MediaArea/MediaArea-Website/blob/master/src/PaymentBundle/Tests/Controller/PaymentFlowTest.php) make payments as a user does in his browser.

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
