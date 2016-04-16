Critical-section functionality for PHP
========================

Allows create a critical section of code where only determine number of process can enter.

Installing
----------

Preferred way to install is with [Composer](https://getcomposer.org/).

Install this library using composer:

```console
$ composer require adilab/critical-section
```

Usage:
-------------
Usage in order to check if the process can entry into critical section.
```php
require('vendor/autoload.php');

use Adi\System\CriticalSection;

$cs = new CriticalSection();
if (!$cs->hasAccess()) 
	die("There are other process in executing...\n");
echo "Processing...\n";
$cs = NULL; // Destructs (closes) the critical section.
```

The process can wait to of the performance critical section by other processes.
```php
require('vendor/autoload.php');

use Adi\System\CriticalSection;

$cs = new CriticalSection();
$cs->waitAccess();
echo "Processing...\n";
```

Documentation
----------


[API documentacion](http://adilab.net/projects/api/namespace-Adi.System.html)