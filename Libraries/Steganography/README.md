Steganography
=============

[![Latest Version](https://poser.pugx.org/maikschneider/steganography/v/stable)](https://packagist.org/packages/maikschneider/steganography)

Simple PHP implementation of Steganography (Hiding a hidden message within an image)

Requirements
------------

* PHP8.1+

Installation
------------

```
composer require maikschneider/steganography
```

Usage
-----

### Put your message into an image

``` php
<?php

use MaikSchneider\Steganography\Processor;

$processor = new Processor();
$image = $processor->encode('/path/to/image.jpg', 'Message to hide'); // jpg|png|gif

// Save image to file
$image->write('/path/to/image.png'); // png only

// Or outout image to stdout
$image->render();
```

### Extract message from an image

``` php
<?php

use MaikSchneider\Steganography\Processor;

$processor = new Processor();
$message = $processor->decode('/path/to/image.png');

echo $message; // "Message to hide"
```

License
-------

The MIT License

Author
------

Kazuyuki Hayashi (@kzykhys)