Mixes getting details of ip and saving it.


## Prerequisites

- Laravel 8
- PHP 8 


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
| detailsBe()                     | Writing details manually.                            |
| prepare()                       | Editting predefined details.                         |

*for Example:*

```php
IpLogger::prepare(function ($details) {
  return $details + ['test' => 'test'];
})->getDetails();
```


## Saving to database

```php
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



## Manually geting details

By default has been wroten two apis to getting details of ip, [ip_api](https://ip-api.com/) ,and [vpn_api](https://vpnapi.io/). It's settable in config file.

Or if you want to use another api get details manually.

```php
IpLogger::detailsBe(function () {
  return [ ... ];
})
```


## Exception handling

Getting last Exception that happened:

```php
IpLogger::getLastException();
```

Or:

```php

protected $listen = [
  AmirHossein5\LaravelIpLogger\Events\Failed::class => [
    IpLoggerFailed::class,
  ]
];

```





## License

[License](LICENSE)
