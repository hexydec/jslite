<?php
declare(strict_types = 1);
namespace hexydec\jslite;
use \hexydec\tokens\tokenise;

class brackets {

	public const significant = true;
	protected $root;
	protected $expressions = [];
	public $bracket = 'bracket'; // square or bracket or curly

	/**
	 * Constructs the comment object
	 *
	 * @param jslite $root The parent jslite object
	 * @param array $scopes An array of variables that are available in this scope, where the key is the variable name and the value is the scope object
	 */
	public function __construct(expression $root) {
		$this->root = $root;
	}

	/**
	 * Parses an array of tokens
	 *
	 * @param array &$tokens A tokenise object
	 * @return void
	 */
	public function parse(tokenise $tokens) : bool {
		if (($token = $tokens->current()) !== false) {
			$this->bracket = mb_substr($token['type'], 4);
			while (($token = $tokens->next()) !== null) {
				if ($token['type'] !== 'comma') {
					$obj = new expression();
					if ($obj->parse($tokens)) {
						$this->expressions[] = $obj;
					}
					if (($token = $tokens->current()) !== null && $token['type'] === 'close'.$this->bracket) {
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Minifies the internal representation of the document
	 *
	 * @param array $minify An array indicating which minification operations to perform, this is merged with htmldoc::$config['minify']
	 * @return void
	 */
	public function minify(array $minify = []) : void {
		$expressions = $this->expressions;

		// minify expressions
		$last = null;
		foreach ($expressions AS $item) {
			$item->minify($minify);

			// get last expression if it contains significant code
			foreach ($item->commands AS $comm) {
				if ($comm::significant) {
					$last = $item;
					break;
				}
			}
		}

		// must not remove eol if for loop
		$commands = $this->root->commands;
		$prev = null;
		foreach ($commands AS $i => $item) {
			if ($item === $this) {
				if ($prev && get_class($prev) === 'hexydec\\jslite\\keyword' && $prev->keyword === 'for') {

					// count expressions where the EOL is ; (Could be comma)
					$count = 0;
					foreach ($expressions AS $expr) {
						if ($expr->eol === ';') {
							$count++;
						}
					}
					if ($count !== 3) {
						$last = null;
					}
				}
				break;
			} elseif ($item::significant) {
				$prev = $item;
			}
		}

		// remove last eol
		if ($last) {
			$last->eol = null;
		}
	}

	/**
	 * Compile as Javascript
	 *
	 * @param array $options An array indicating output options
	 * @return string The compiled HTML
	 */
	public function compile(array $options = []) : string {
		$brackets = [
			'square' => ['[', ']'],
			'bracket' => ['(', ')'],
			'curly' => ['{', '}'],
		];
		$bracket = $brackets[$this->bracket];
		$js = '';
		if ($this->expressions) {
			foreach ($this->expressions AS $key => $item) {
				$js .= $item->compile($options);
			}
			$item->eol = null;
		}
		return $bracket[0].$js.$bracket[1];
	}
}
