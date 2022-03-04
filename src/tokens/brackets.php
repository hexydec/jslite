<?php
declare(strict_types = 1);
namespace hexydec\jslite;
use \hexydec\tokens\tokenise;

class brackets {

	public const significant = true;
	protected expression $root;
	protected array $expressions = [];
	public string $bracket = 'bracket'; // square or bracket or curly

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
		if (($token = $tokens->current()) !== null) {
			$bracket = $this->bracket = \mb_substr($token['type'], 4);
			while (($token = $tokens->next()) !== null) {
				$obj = new expression($bracket);
				if ($obj->parse($tokens)) {
					$this->expressions[] = $obj;
				}
				if (($token = $tokens->current()) !== null && $token['type'] === 'close'.$bracket) {
					return true;
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

			// get last expression that contains significant code
			if ($minify['semicolons']) {
				foreach ($item->commands AS $comm) {
					if ($comm::significant) {
						$last = $item;
						break;
					}
				}
			}
		}

		// other checks before we remove semi-colon
		if ($last && $minify['semicolons']) {
			$key = __NAMESPACE__.'\\keyword';
			$bra = __NAMESPACE__.'\\brackets';

			// don't remove semi-colon from keyword + brackets with no following commands
			if ($this->bracket === 'curly') {
				$sigcomms = [];
				foreach ($last->commands AS $comm) {
					if ($comm::significant) {
						$sigcomms[] = $comm;
					}
				}
				if (count($sigcomms) === 2 && \get_class($sigcomms[0]) === $key && $sigcomms[0]->content !== 'return' && \get_class($sigcomms[1]) === $bra && $sigcomms[1]->bracket === 'bracket') {
					return;
				}
			}

			// must not remove eol if for loop
			$prev = null;
			foreach ($this->root->commands AS $i => $item) {
				if ($item === $this) {
					if ($prev && \get_class($prev) === $key && $prev->content === 'for') {

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
	}

	/**
	 * Compile as Javascript
	 *
	 * @return string The compiled HTML
	 */
	public function compile() : string {

		// compile child expressions
		$js = '';
		foreach ($this->expressions AS $key => $item) {
			$js .= $item->compile();
		}

		// wrap in brackets
		$brackets = [
			'square' => ['[', ']'],
			'bracket' => ['(', ')'],
			'curly' => ['{', '}'],
		];
		$bracket = $brackets[$this->bracket];
		return $bracket[0].$js.$bracket[1];
	}
}
