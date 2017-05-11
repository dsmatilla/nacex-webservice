# Nacex webservice

Nacex webservice controller for PHP

## Installation

You can add this library to your project using [Composer](https://getcomposer.org/):

    composer require dsmatilla/nacex-webservice
    
### Usage

Nacex class can be used to communicate with Nacex webservice and manage expeditions:

```php
use dsmatilla\NacexWebservice\Nacex;

$nacex = new Nacex("USER", "PASSWORD")
```
