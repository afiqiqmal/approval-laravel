# Approval Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/afiqiqmal/approval-laravel.svg?style=flat-square)](https://packagist.org/packages/afiqiqmal/approval-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/afiqiqmal/approval-laravel.svg?style=flat-square)](https://packagist.org/packages/afiqiqmal/approval-laravel)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/paypalme/mhi9388?locale.x=en_US)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require afiqiqmal/approval-laravel
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Afiqiqmal\Approval\ApprovalServiceProvider" --tag="migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Afiqiqmal\Approval\ApprovalServiceProvider" --tag="config"
```

## Usage

#### Add `RequireApproval` trait to the model

```php
class Entity extends Model
{
    use RequireApproval;

    //plenty of public function can be customize
}
```

#### Add `HasApprovable` trait to the User Model
```php
class User extends Authenticable
{
    use HasApprovable;
    //...
    //...
}
```

#### Query

Include all items including not approve

```php
Model::getQuery()->includeNotApprove()->get(); 
```

List all items with not approve

```php
Model::getQuery()->onlyNotApprove()->get(); 
```



## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Hafiq](https://github.com/afiqiqmal)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

<a href="https://www.paypal.com/paypalme/mhi9388?locale.x=en_US"><img src="https://i.imgur.com/Y2gqr2j.png" height="40"></a>  
