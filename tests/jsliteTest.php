<?php
use hexydec\jslite\jslite;

final class jsliteTest extends \PHPUnit\Framework\TestCase {

	public function testCanStripStartAndEnd() {
		$tests = [
			[
				'input' => '
					  var item = 42;',
				'output' => 'var item=42;'
			],
			[
				'input' => 'var item = 42;

				   ',
				'output' => 'var item=42;'
			]
		];
		$this->compareMinify($tests);
	}

	public function testCanStripMultilineComments() {
		$tests = [
			[
				'input' => 'var item = 42; /* comment */',
				'output' => 'var item=42;'
			],
			[
				'input' => 'var item  /* comment */  = 42;',
				'output' => 'var item=42;'
			],
			[
				'input' => 'var it/* comment */em    = 42;',
				'output' => 'var item=42;'
			],
			[
				'input' => 'var item = 42; /* multi line

				comment */',
				'output' => 'var item=42;'
			],
			[
				'input' => 'var item  /* multi line

				comment */  = 42;',
				'output' => 'var item=42;'
			],
			[
				'input' => 'var it/* multi line

				comment */em    = 42;',
				'output' => 'var item=42;'
			],
			[
				'input' => '
					/** multi line
					 *
					 * @param string item The number 42
 				 	 */
				var item    = 42;',
				'output' => 'var item=42;'
			]
		];
		$this->compareMinify($tests);
	}

	public function testCanStripSinglelineComments() {
		$tests = [
			[
				'input' => 'var   item = "test  this"   ;   ',
				'output' => 'var item="test  this";'
			],
			[
				'input' => 'export function   ( item1  , item2  )
				{
					return item1  *  item2;
				}',
				'output' => 'export function(item1,item2){return item1*item2;}'
			]
		];
		$this->compareMinify($tests);
	}

	public function testCanProtectQuotesStrings() {
		$tests = [
			[
				'input' => 'var   item = "test  this"   ;   ',
				'output' => 'var item="test  this";'
			],
			[
				'input' => 'export function   ( item1  , item2  )
				{
					return item1  *  item2;
				}',
				'output' => 'export function(item1,item2){return item1*item2;}'
			]
		];
		$this->compareMinify($tests);
	}

	public function testCanStripWhitespaceAroundControlChars() {
		$tests = [
			[
				'input' => 'var   item = "test  this"   ;   ',
				'output' => 'var item="test  this";'
			],
			[
				'input' => 'export function   ( item1  , item2  )
				{
					return item1  *  item2;
				}',
				'output' => 'export function(item1,item2){return item1*item2;}'
			]
		];
		$this->compareMinify($tests);
	}

	public function testCanStripWhitespaceAroundIncrements() {
		$tests = [
			[
				'input' => 'var   item = "test  this"   ;   ',
				'output' => 'var item="test  this";'
			],
			[
				'input' => 'export function   ( item1  , item2  )
				{
					return item1  *  item2;
				}',
				'output' => 'export function(item1,item2){return item1*item2;}'
			]
		];
		$this->compareMinify($tests);
	}

	public function testCanCompressWhitespace() {
		$tests = [
			[
				'input' => 'var   item = "test  this"   ;   ',
				'output' => 'var item="test  this";'
			],
			[
				'input' => 'export function   ( item1  , item2  )
				{
					return item1  *  item2;
				}',
				'output' => 'export function(item1,item2){return item1*item2;}'
			]
		];
		$this->compareMinify($tests);
	}

	protected function compareMinify(array $tests) {
		$obj = new jslite();
		foreach ($tests AS $item) {
			$obj->load($item['input']);
			$obj->minify();
			$this->assertEquals($item['output'], $obj->save());
		}
	}
}
