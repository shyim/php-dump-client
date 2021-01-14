# PHP Dump Client

This is Symfony Var-Dump Server in fancy. This repository holds the code for the PHP Client to send informations to the UI Server.

## This is project is currently in work

## Install

```bash
composer req shyim/php-dump-client
```

or Prefixed (Without requiring other dependencies) 

```bash
composer req shyim/php-dump-client-prefixed
```

or `auto_prepend_file` globally useable

* Clone `shyim/php-dump-client-prefixed` somewhere
* Configure `auto_prepend_file=PREFIXED_FOLDER/prepend.php`

## Usage

* Start the [Debug Server first](https://github.com/shyim/php-dump-server)
* Optional: Set environment `PHP_DUMP_SERVER_URL` to the Dump Server if the dump server runs not local
* Use your favourite `pd()` Command


```php
// Sends variables to the UI Server to show
pd()->log($var1, $var2);

// Sends the trace to the UI
pd()->trace();

// Clears the ui window
pd()->clear();

// Stops the process until its unlocked in the UI
pd()->pause();

// Show execution time of function and memory usage
pd()->time('Label', function() {
  sleep(1);
});

// Show execution time of function and memory usage
$timer = pd()->time('Label');

// Do something

$timer->stop();

// Allows tagging of calls in the UI
pd()->tag('My-Tag')->Method($args);
```
