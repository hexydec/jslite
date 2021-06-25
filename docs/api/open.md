# open()

Open a Javascript file from a URL.

```php
$doc = new \hexydec\jslite\jslite();
$doc->open($url, $context = null, &$error = null);
```

## Arguments

| Parameter	| Type		| Description 														|
|-----------|-----------|-------------------------------------------------------------------|
| `$url`	| String 	| The URL of the Javascript document to be opened					|
| `$context`| Resource 	| A stream context resource created with `stream_context_create()`	|
| `$error`	| String	| A reference to a description of any error that is generated.		|

## Returns

A string containing the Javascript that was loaded, or `false` when the requested file could not be loaded.
