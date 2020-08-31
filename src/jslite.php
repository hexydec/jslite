<?php
declare(strict_types = 1);
namespace hexydec\jslite;

class jslite {

	protected $js;

	public function __construct() {
	}

	/**
	 * Open an Javascript file from a URL
	 *
	 * @param string $url The address of the Javascript file to retrieve
	 * @param resource $context An optional array of context parameters
	 * @param string &$error A reference to any user error that is generated
	 * @return mixed The loaded HTML, or false on error
	 */
	public function open(string $url, $context = null, string &$error = null) {

		// open a handle to the stream
		if (($handle = @fopen($url, 'rb', false, $context)) === false) {
			$error = 'Could not open file "'.$url.'"';

		// retrieve the stream contents
		} elseif (($html = stream_get_contents($handle)) === false) {
			$error = 'Could not read file "'.$url.'"';

		// success
		} else {

			// find charset in headers
			$charset = null;
			$meta = stream_get_meta_data($handle);
			if (!empty($meta['wrapper_data'])) {
				foreach ($meta['wrapper_data'] AS $item) {
					if (mb_stripos($item, 'Content-Type:') === 0 && ($charset = mb_stristr($item, 'charset=')) !== false) {
						$charset = mb_substr($charset, 8);
						break;
					}
				}
			}

			// load html
			if ($this->load($html, $charset, $error)) {
				return $html;
			}
		}
		return false;
	}

	/**
	 * Parse an Javascript string into the object
	 *
	 * @param string $js A string containing valid Javascript
	 * @param string $charset The charset of the document
	 * @param string &$error A reference to any user error that is generated
	 * @return bool Whether the input HTML was parsed
	 */
	public function load(string $js, string $charset = null, &$error = null) : bool {
		$this->js = $js;
		return true;
	}

	/**
	 * Minifies the internal representation of the document
	 *
	 * @param array $minify An array indicating which minification operations to perform, this is merged with htmldoc::$config['minify']
	 * @return void
	 */
	public function minify(array $minify = []) : void {

		$patterns = [
			'startend' => '^\\s++', // whitespace at start
			'end' => '\\s++$', // whitespace at end
			'commentmulti' => '\\s*+\\/\\*(?:(?U)[\\s\\S]*)\\*\\/', // multiline comments
			'commentsingle' => '\\s*+\\/\\/[^\\n]*+', // single line comment
			'doublequotes' => '"(?:\\\\.|[^\\\\"])*"', // anything in unescaped quotes
			'singlequotes' => "'(?:\\\\.|[^\\\\'])*'", // anything in unescaped quotes
			'regexp' => '(?<=return|[^a-z0-9])\\s*+\\/(?:\\\\.|[^\\\\\\/\\n\\r])*\\/[gimsuy]?', // regular expressions
			'increment' => '(?:\\+\\+|--)\\s++(?=[+\\/+-])',
			'control' => '\\s*+[=+*\\/;(){}\\[\\],><|:-]\\s*+', // whitespace around control characters
			'keywords' => '(?<=[a-z0-9"\'])(\\s++)(?=[a-z0-9"\'])',
			'whitespace' => '\\s++' // two or more whitespace characters
		];

		// replace captures
		$compiled = '/'.implode('|', $patterns).'/i';
		$this->js = preg_replace_callback($compiled, function ($match) {

			// skip if quotes
			if (mb_strpos($match[0], '"') !== 0 && mb_strpos($match[0], "'") !== 0) {

				// remove whitespace around capture
				$match[0] = trim($match[0]);

				// strip comments
				if (mb_strpos($match[0], '//') === 0 || mb_strpos($match[0], '/*') === 0) {
					$match[0] = '';

				// handle increments and decrements next to other operators
				} elseif ($match[0] === '++' || $match[0] === '--') {
					$match[0] .= ' '; // restore space

				// add a single space if capture was only whitespace not at the start or end of the string
				} elseif ($match[0] === '' && !empty($match[1])) {
					$match[0] = mb_strpos($match[1], "\n") === false ? ' ' : "\n";
				}
			}
			return $match[0];
		}, $this->js);
	}

	/**
	 * Output or save the javascript
	 *
	 * @return string The minified javascript
	 */
	public function save(string $file = null) {
		if (!$file) {
			return $this->js;
		} elseif (file_put_contents($file, $this->js) === false) {
			trigger_error('File could not be written', E_USER_WARNING);
		} else {
			return true;
		}
		return false;
	}
}
