# Non-Audit logs for your Laravel models

[![Latest Version on Packagist](https://img.shields.io/packagist/v/syberisle/laravel-scribe.svg?style=flat-square)](https://packagist.org/packages/syberisle/laravel-scribe)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/syberisle/laravel-scribe/run-tests.yml?branch=main&label=Tests)](https://github.com/syberisle/laravel-scribe/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/syberisle/laravel-scribe.svg?style=flat-square)](https://packagist.org/packages/syberisle/laravel-scribe)

An opinionated package that provides easy to use functions to log diagnostic information against the models
in your app. These logs are stored in their own tables. 

If you are looking for automatic event logging on your models, please look at either 
[spatie/laravel-activitylog](https://github.com/spatie/laravel-activitylog) or 
[owen-it/auditable](https://github.com/owen-it/laravel-auditing) as they are better suited for an audit log.

You can use it like this:
```php
SomeModel::find(1)->log('hi there');

// retrieving model logs
SomeModel::find(1)->logs();
// or
SomeModelLogs::all();
```

## Installation

```shell
composer install syberisle/laravel-scribe
```

You can then create log models for your existing models:
```php
php artisan make:scribe:model 'App\Models\SomeModel'
```

_Note:_ This automatically creates the migration for the log model, you may specify `--no-migration` if you do not want 
the migration to be auto-generated.

You will also need to update your model to get the `logs()`, and `log()` methods.
```php

use SyberIsle\Laravel\Scribe\Model\HasLogs;

class MyModel
{
    use HasLogs;
    
    protected $logModel = MyModelLog::class
    ...
}
```

### UUID support

UUID's are supported for the migrations:

  * `--uuid` will cause the migration & generated Log class to use UUID's via the `HasUuids` trait
  * `--causer-uuid` will cause the migration to utilize UUID's when creating the `causer` columns
  * The subject ID will be auto-detected by the Model generator, and will apply UUID's if necessary to the `subject_id`

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you've found a bug regarding security please report it via the security tab of this repository.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.