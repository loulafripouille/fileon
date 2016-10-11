# fileon [![Build Status](https://travis-ci.org/laudeon/fileon.svg?branch=master)](https://travis-ci.org/laudeon/fileon)
PHP.7 file watcher

## Install
using [composer](https://getcomposer.org/doc/00-intro.md): `composer require laudeon/fileon`

## Getting started
```php
use Fileon\Resource;
use Fileon\Watcher;

$resource = new Resource(_DIR_);
$watcher = new Watcher($resource);

$watcher->onNew(function(\SplFileInfo $file){
    //...
});
$watcher->onModified(function(\SplFileInfo $file){
    //...
});

$watcher->watch(function() use ($watcher) {
  if(...) {
      $watcher->stop();
  }
});
```

## API

### Watcher

#### Watcher::__construct(Fileon\Resource $resource [, integer $sleep])
- `$resource` is an instance of Fileon\Resource.
- `$sleep`, optional, is the sleep time on each loop turn of the watcher, in microseconds. Defautl is 1000000
