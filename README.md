# JSlite: PHP Javascript Minifier
A simplistic Javascript minifier designed for compressing inline scripts on the fly, written in PHP.

![Licence: MIT](https://img.shields.io/badge/Licence-MIT-lightgrey.svg)
![Status: Alpha](https://img.shields.io/badge/Status-Alpha-red.svg)

**This is currently alpha grade software - do not deploy into production**

## Description

Designed to compliment [HTMLdoc](http://githubcom/hexydec/htmldoc), this program is capable of performing very simple and fast minification of Javascript, and is aimed at removing whitespace and comments from inline javascripts within an HTML file.

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
	echo $doc->save();
}
```

## Contributing

If you find an issue with JSlite, please create an issue in the tracker.

If you wish to fix an issue yourself, please fork the code, fix the issue, then create a pull request, and I will evaluate your submission.

## Licence

The MIT License (MIT). Please see [License File](LICENCE) for more information.
