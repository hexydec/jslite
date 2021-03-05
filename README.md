# JSlite: PHP Javascript Minifier
A simplistic Javascript minifier designed for compressing inline scripts on the fly, written in PHP.

![Licence: MIT](https://img.shields.io/badge/Licence-MIT-lightgrey.svg)
![Status: Beta](https://img.shields.io/badge/Status-Beta-Yellow.svg)

**This project is currently in beta, so you should test your implementation thoroughly before deployment**

## Description

Designed to compliment [HTMLdoc](http://githubcom/hexydec/htmldoc), this program is capable of performing very simple and fast minification of Javascript, and is aimed at removing whitespace and comments from inline Javascripts within an HTML file.

The software is implemented as a compiler to make sure it is reliable, and comes with a full test suite.

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
## Minification

No code is rewritten, only whitespace and comments are removed, plus other minor code optimisations. The following optimisations are performed:

- Whitespace stripped from beginning and end of string
- Multiline comments removed
- Single line comments removed
- Whitespace removed around control characters
- Whitespace collapsed between expressions
- Whitespace around increments/decrements are handled correctly
- Trailing semi-colons removed
- Quotes are preserved
- Regular Expressions are preserved

## Contributing

If you find an issue with JSlite, please create an issue in the tracker.

If you wish to fix an issue yourself, please fork the code, fix the issue, then create a pull request, and I will evaluate your submission.

## Licence

The MIT License (MIT). Please see [License File](LICENCE) for more information.
