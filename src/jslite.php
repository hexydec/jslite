<?php
declare(strict_types = 1);
namespace hexydec\jslite;
use \hexydec\tokens\tokenise;

class jslite {

	/**
	 * @var array $tokens Regexp components keyed by their corresponding codename for tokenising HTML
	 */
	protected static $tokens = [

		// consume strings in quotes, check for escaped quotes
		'doublequotes' => '"(?:\\\\[^\\n\\r]|[^\\\\"\\n\\r])*+"',
		'singlequotes' => "'(?:\\\\[^\\n\\r]|[^\\\\'\\n\\r])*+'",
		'templateliterals' => '`(?:\\\\.|[^\\\\`])*+`',

		// look behind for keyword|value|variable and capture whitespace (replaced with space or linebreak), or just whitespace which will be removed, followed by a single line regular expressionn, optional whitespace (Will be removed), and then must be followed by a control character or linebreak
		'regexp' => '\\/(?!\\*)(?:\\\\.|[^\\\\\\/\\n\\r])*\\/[gimsuy]?[ \\t]*+(?=[.,;\\)\\]}]|[\\r\\n]|$)', // (?:(?<=[\\p{L}\\p{Nl}\\p{Mn}\\p{Mc}\\p{Nd}\\p{Pc}_$"\'])\\s++|\s*+)

		// capture single line comments after quotes incase it contains //
		'commentsingle' => '\\/\\/[^\\n]*+',

		// remove multiline comments
		'commentmulti' => '\\/\\*(?:(?U)[\\s\\S]*)\\*\\/',

		'increment' => '\\+\\+|--',

		'keyword' => '\\b(?:let|break|case|catch|class|const|continue|debugger|default|delete|do|else|export|extends|finally|for|function|if|import|in|instanceof|new|return|super|switch|this|throw|try|typeof|var|void|while|with|yield|null|async|await)\\b',
		'variable' => '[\\p{L}\\p{Nl}$_][\\p{L}\\p{Nl}\\p{Mn}\\p{Mc}\\p{Nd}\\p{Pc}$_]*+',
		'number' => '(?:0[bB][01_]++n?|0[oO][0-7_]++n?|0[xX][a-f0-9_]|[0-9][0-9_]*+(?:\\.[0-9_]++)?(?:e[+-]?[1-9][0-9]*+)?)',

		'eol' => ';',
		// 'dot' => '\\.',
		'comma' => ',',
		'operator' => '[+*\\/<>%&-]?=|[\\.+*!<>:%|&?^-]+|\\/',
		'opensquare' => '\\[',
		'closesquare' => '\\]',
		'openbracket' => '\\(',
		'closebracket' => '\\)',
		'opencurly' => '\\{',
		'closecurly' => '\\}',
		'whitespace' => '\\s++',
		'other' => '.'
	];

	protected $config = [
		'minify' => [
			'whitespace' => true, // strip whitespace around javascript
			'comments' => true, // strip comments
			'eol' => true, // remove end of line semi-colons where possible
			'quotestyle' => '"' // convert quotes to the specified character, null or false not to convert
		]
	];
	protected $expressions = null;

	public function __construct(array $config = []) {
		if ($config) {
			$this->config = \array_replace_recursive($this->config, $config);
		}
	}

	/**
	 * Retrieves the requested value of the object configuration
	 *
	 * @param string ...$key One or more array keys indicating the configuration value to retrieve
	 * @return mixed The value requested, or null if the value doesn't exist
	 */
	public function getConfig(string ...$keys) {
		$config = $this->config;
		foreach ($keys AS $item) {
			if (isset($config[$item])) {
				$config = $config[$item];
			} else {
				return null;
			}
		}
		return $config;
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
		if (($js = \file_get_contents($url, $context)) === false) {
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
	 * @param string &$error A reference to any user error that is generated
	 * @return bool Whether the input HTML was parsed
	 */
	public function load(string $js, string &$error = null) : bool {

		// reset the document
		$this->children = [];

		// tokenise the input Javascript
		$tokens = new tokenise(self::$tokens, $js);
		// while (($token = $tokens->next()) !== null) {
		// 	var_dump($token);
		// }
		// exit();

		// parse the document
		if (($this->expressions = $this->parse($tokens)) === null) {
			$error = 'Input is not valid';

		// success
		} else {
			return true;
		}
		return false;
	}

	protected function parse(tokenise $tokens) : array {
		$expressions = [];
		while (($token = $tokens->next()) !== null) {
			$obj = new expression($this);
			if ($obj->parse($tokens)) {
				$expressions[] = $obj;
			}
		}
		return $expressions ? $expressions : null;
	}

	/**
	 * Minifies the internal representation of the document
	 *
	 * @param array $minify An array indicating which minification operations to perform, this is merged with htmldoc::$config['minify']
	 * @return void
	 */
	public function minify(array $minify = []) : void {

		// merge config
		$minify = \array_replace_recursive($this->config['minify'], $minify);

		// minify expressions
		$last = null;
		$not = ['whitespace', 'comment'];
		foreach ($this->expressions AS $item) {
			$item->minify($minify);

			// get last expression if it contains significant code
			if ($minify['eol']) {
				foreach ($item->commands AS $comm) {
					if ($comm::significant) {
						$last = $item;
						break;
					}
				}
			}
		}

		// remove last EOL
		if ($last) {
			$last->eol = null;
		}
	}

	/**
	 * Compile the document as an HTML string
	 *
	 * @param array $options An array indicating output options, this is merged with htmldoc::$output
	 * @return string The compiled Javascript
	 */
	public function compile(array $options = []) : string {
		$js = '';
		foreach ($this->expressions AS $item) {
			$js .= $item->compile($options);
		}
		return $js;
	}
}
