# compile()

Compile the document into a Javascript string and save to the specified location, or return as a string.

```php
$doc = new \hexydec\jslite\jslite();
if ($doc->load($js)) {
	$doc->compile();
}
```

## Returns

Returns the compiled Javascript as a string.
