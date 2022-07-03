Mixes getting details of ip and saving it.


## Prerequisites

- Laravel ```^8.0|^9.0```
- PHP 8 
- guzzlehttp/guzzle: ```^6.3.1|^7.0.1```


## Installation

```bash
composer require amir-hossein5/laravel-ip-logger
```

and for publishing configuration file: 

```bash
php artisan vendor:publish --tag ipLogger
```


## Usage

For just getting details:

```php
use AmirHossein5\LaravelIpLogger\Facades\IpLogger;

IpLogger::getDetails();
```

Methods for work with details:

| method                          | description                                          |
|---------------------------------|------------------------------------------------------|
| detailsBe()                     | [Writing details manually](#manually-getting-details).|
| prepare()                       | Editting predefined details.                         |

*for Example:*

```php
use AmirHossein5\LaravelIpLogger\Facades\IpLogger;

IpLogger::prepare(function ($details) {
  return $details + ['test' => 'test'];
})->getDetails();
```


## Saving to database

```php
use AmirHossein5\LaravelIpLogger\Facades\IpLogger;

IpLogger::model(ModelName::class)
  ->updateOrCreate(
    fn ($details) => [
      'ip'        => $details['query']
    ],
    fn ($details) => [
      'continent' =>  $details['continent'],
      'country'   =>  $details['country'],
      ...
    ],
);
```

For saving there are two methods, ```create```, ```updateOrCreate```, and work like laravel ones.



## Manually getting details

By default has been wroten two apis to getting details of ip, [ip_api](https://ip-api.com/) ,and [vpn_api](https://vpnapi.io/). It's settable in config file.

Or if you want to use another api get details manually.

```php
use \AmirHossein5\LaravelIpLogger\Facades\IpLogger;

IpLogger::detailsBe(function () {
  return [ ... ];
})->updateOrCreate(...);
```


## Exception Handling

Except exceptions that when saving to database(e.g, create, updateOrCreate) happens, can be handle by using:

### Getting Last Exception That Happened

```php
use AmirHossein5\LaravelIpLogger\Facades\IpLogger;

IpLogger::getLastException();
```

### Catching Exceptions Inline

```php
use AmirHossein5\LaravelIpLogger\Facades\IpLogger;

IpLogger::catch(function ($exception) {
  // send mail ...
})...;
```
> You should use this as the first method.

> When using this way no event will be dispatch.

### Listening For Exceptions

```php
/**
 * The event listener mappings for the application.
 *
 * @var array
 */
protected $listen = [
  AmirHossein5\LaravelIpLogger\Events\Failed::class => [
    IpLoggerFailed::class,
  ]
];

```



## License

[License](LICENSE)

