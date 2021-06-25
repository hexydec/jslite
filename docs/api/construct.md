# \__construct()

Called when a new jslite object is created.

```php
$doc = new \hexydec\jslite\jslite($config);
```
## Arguments

### `$config`

A optional array of configuration options that will be merged recursively with the default configuration. The available options and their defaults are:

#### minify

An array of minification defaults. It is recommended that this is passed to the [`minify()` method](minify.md) instead.

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

A new JSlite object.
