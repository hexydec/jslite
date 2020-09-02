<?php
declare(strict_types = 1);
namespace hexydec\jslite;

class jslite {

	/**
	 * @var string $js Stores the Javascript to be minified
	 */
	protected $js;

	/**
	 * @var array $config Stores default minification options
	 */
	protected $config = [
		'commentsingle' => true,
		'commentmulti' => true,
		'whitespace' => true,
		'semicolon' => true
	];

	protected $tokens = [

		// remove multiline comments
		'commentmulti' => '\\/\\*(?:(?U)[\\s\\S]*)\\*\\/',

		// consume strings in quotes, check for escaped quotes
		'doublequotes' => '"(?:\\\\.|[^\\\\"])*"',
		'singlequotes' => "'(?:\\\\.|[^\\\\'])*'",

		// capture single line comments after quotes incase it contains //
		'commentsingle' => '\\/\\/[^\\n]*+',

		// look behind for keyword|value|variable and capture whitespace (replaced with space or linebreak), or just whitespace which will be removed, followed by a single line regular expressionn, optional whitespace (Will be removed), and then must be followed by a control character or linebreak
		'regexp' => '(?:((?<=[\\p{L}\\p{Nl}\\p{Mn}\\p{Mc}\\p{Nd}\\p{Pc}_$"\'])\\s++)|\\s++)\\/(?:\\\\.|[^\\\\\\/\\n\\r])*\\/[gimsuy]?[ \\t]*+(?=[.,;\\)\\]}]|[\\r\\n]|$)',

		// capture a special case when an increment or decrement is next to a plus or minus
		'increment' => '(?<=\\+)[ \\t]++\\+\\+',
		'decrement' => '(?<=-)[ \\t]++--',

		// capture whitespace in between keyword|value|variable|quotes and reduce to single space or linebreak
		'keywords' => '(?<=[\\p{L}\\p{Nl}\\p{Mn}\\p{Mc}\\p{Nd}\\p{Pc}_$"\'])(\\s++)(?=[\\p{L}\\p{Nl}_$"\'])',

		// must not consume control character incase it is the start of a regexp
		'precontrol' => '\\s++(?=[.,:;|&?<>%^()\\[\\]{}=+*\\/-])', // whitespace around control characters
		'postcontrol' => '(?<=[.,:;|&?<>%^()\\[\\]{}=+*\\/-])\\s++',

		// whitespace at start and end of the input
		'startend' => '^\\s++',
		'end' => '\\s++$'
	];

	public function __construct(array $config = []) {
		if ($config) {
			$this->config = array_replace_recursive($this->config, $config);
		}
	}

	/**
	 * Open an Javascript file from a URL
	 *
	 * @param string $url The address of the Javascript file to retrieve
	 * @param resource $context An optional array of context parameters
	 * @param string &$error A reference to any user error that is generated
	 * @return mixed The loaded Javascript, or false on error
	 */
	public function open(string $url, $context = null, string &$error = null) {
		if (($js = file_get_contents($url, $context)) === false) {
			$error = 'Could not open file "'.$url.'"';
		} elseif ($this->load($js)) {
			return $js;
		}
		return false;
	}

	/**
	 * Parse an Javascript string into the object
	 *
	 * @param string $js A string containing valid Javascript
	 * @return bool Whether the input HTML was parsed
	 */
	public function load(string $js) : bool {
		$this->js = $js;
		return (bool) $this->js;
	}

	/**
	 * Minifies the internal representation of the document
	 *
	 * @param array $minify An array indicating which minification operations to perform, this is merged with htmldoc::$config['minify']
	 * @return void
	 */
	public function minify(array $minify = []) : void {
		$minify = array_merge($this->config, $minify);

		// get capture patterns
		$patterns = $this->tokens;

		// remove comments first as they could be in the middle of something
		if ($minify['commentmulti']) {
			$this->js = preg_replace('/'.$patterns['commentmulti'].'/i', '', $this->js);
			unset($patterns['commentmulti']);
		}

		// remove single line comments, but extract out quotes so we don't capture the wrong thing
		if ($minify['commentsingle']) {
			$this->js = preg_replace_callback('/'.implode('|', array_intersect_key($patterns, [
				'singlequotes' => true,
				'doublequotes' => true,
				'commentsingle' => true
			])).'/i', function ($match) {
				return mb_strpos($match[0], '//') === 0 ? '' : $match[0];
			}, $this->js);
			unset($patterns['commentsingle']);
		}

		// remove whitespace
		if ($minify['whitespace']) {

			// replace captures
			$compiled = '/'.implode('|', $patterns).'/i';
			$this->js = preg_replace_callback($compiled, function ($match) {

				// remove whitespace around capture
				$match[0] = trim($match[0]);

				// add a single space or linebreak to separate keywords
				if (!empty($match[1]) || !empty($match[2])) {
					$match[0] = (mb_strpos($match[1].($match[2] ?? ''), "\n") === false ? ' ' : "\n").$match[0];

				// handle increments and decrements next to other operators
				} elseif ($match[0] === '++' || $match[0] === '--') {
					$match[0] = ' '.$match[0]; // restore space

				// add line break where comments are allowed
				} elseif (mb_strpos($match[0], '//') === 0) {
					$match[0] .= "\n";
				}
				return $match[0];
			}, $this->js);
		}

		// remove trailing semi-colons
		if ($minify['semicolon']) {
			$this->js = str_replace([';)', ';}'], [')', '}'], $this->js);
		}
	}

	/**
	 * Output or save the javascript
	 *
	 * @return string The minified javascript
	 */
	public function save(string $file = null, string &$error = null) {
		if (!$file) {
			return $this->js;
		} elseif (file_put_contents($file, $this->js) === false) {
			$error = 'File could not be written';
		} else {
			return true;
		}
		return false;
	}
}
