# JSlite: PHP Javascript Minifier
A Javascript compiler designed for minifying inline scripts, written in PHP.

![Licence: MIT](https://img.shields.io/badge/Licence-MIT-lightgrey.svg)
![Status: Beta](https://img.shields.io/badge/Status-Beta-Yellow.svg)
[![Tests Status](https://github.com/hexydec/jslite/actions/workflows/tests.yml/badge.svg)](https://github.com/hexydec/jslite/actions/workflows/tests.yml)
[![Code Coverage](https://codecov.io/gh/hexydec/jslite/branch/master/graph/badge.svg)](https://app.codecov.io/gh/hexydec/jslite)

**This project is currently in beta, so you should test your implementation thoroughly before deployment**

## Description

Designed to compliment [HTMLdoc](http://github.com/hexydec/htmldoc), JSlite is a Javascript compiler and minifier, designed for minifying inline Javascript on the fly. It can also be used for compressing larger documents.

The software is implemented as a compiler to ensure reliable, and comes with a full test suite.

## Usage

To minify Javascript:

```php
use hexydec\jslite\jslite;

$doc = new jslite();

// load from a variable
if ($doc->load($javascript) {

	// minify the document
	$doc->minify();

	// retrieve the javascript
	echo $doc->compile();
}
```

You can test out the minifier online at [https://hexydec.com/jslite/](https://hexydec.com/jslite/), or run the supplied `index.php` file after installation.

## Installation

The easiest way to get up and running is to use composer:

```
$ composer install hexydec/jslite
```

## Test Suite

You can run the test suite like this:

### Linux
```
$ vendor/bin/phpunit
```
### Windows
```
> vendor\bin\phpunit
```

## Support

JSlite supports PHP version 7.4+.

## Documentation

- [API Reference](docs/api/readme.md)

## Contributing

If you find an issue with JSlite, please create an issue in the tracker.

If you wish to fix an issue yourself, please fork the code, fix the issue, then create a pull request, and I will evaluate your submission.

## Licence

The MIT License (MIT). Please see [License File](LICENCE) for more information.
