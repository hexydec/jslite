# load()

Loads the inputted Javascript as a document.

```php
$doc = new \hexydec\jslite\jslite();
$doc->load($js, &$error = null);
```

## Arguments

| Parameter	| Type		| Description 											|
|-----------|-----------|-------------------------------------------------------|
| `$js`		| String	| The Javascript to be parsed into the object			|
| `$error`	| &?String	| A reference to any user error that is generated		|

## Returns

A boolean indicating whether the Javascript was parsed successfully.
