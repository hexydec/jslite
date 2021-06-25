# minify()

Minifies the currently loaded document.

```php
$doc = new \hexydec\jslite\jslite();
if ($doc->load($js)) {
	$doc->minify($minify);
}
```

## Arguments

### `$minify`

An array of minification options:

| key			| Description												| Type		| Default	|
|---------------|-----------------------------------------------------------|-----------|-----------|
| whitespace	| Strip whitespace around javascript						| Boolean	| `true`	|
| comments		| Strip comments											| Boolean	| `true`	|
| semicolons	| Remove end of line semi-colons where possible				| Boolean	| `true`	|
| quotestyle	| Convert quotes to the specified character, `null` not to convert | ?String | `"`	|
| booleans		| Shorten booleans, `true` => `!0` and `false` => `!1`		| Boolean	| `true`	|
| undefined		| Convert `undefine`d to `void 0`							| Boolean	| `true`	|
| numbers		| Remove underscores from numbers							| Boolean	| `true`	|

## Returns
