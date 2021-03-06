<?php
	use Adepto\SniffArray\Sniff\SplSniffer;

	class SplSnifferTest extends PHPUnit_Framework_TestCase {
		public function testStaticValidTypes() {
			$this->assertTrue(SplSniffer::isValidType('string'));
			$this->assertTrue(SplSniffer::isValidType('bool'));
			$this->assertTrue(SplSniffer::isValidType('mixedArray'));
			$this->assertTrue(SplSniffer::isValidType('number'));
			$this->assertTrue(SplSniffer::isValidType('mixed'));
			$this->assertTrue(SplSniffer::isValidType('int'));
			$this->assertTrue(SplSniffer::validateTypes([
				'string',
				'boolean',
				'integer',
				'any',
				'array',
				'empty'
			]));

			$this->assertFalse(SplSniffer::isValidType('nope'));
			$this->assertFalse(SplSniffer::validateTypes([
				'no',
				'oh no',
				'bool',
				'not at all',
				'nah'
			]));
		}
	}
