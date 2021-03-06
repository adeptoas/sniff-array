<?php
	use Adepto\SniffArray\Sniff\{
		MixedArraySniffer, SplSniffer
	};

	class MixedArraySnifferTest extends PHPUnit_Framework_TestCase {
		/** @var SplSniffer */
		protected $sniffer;

		/** @var SplSniffer */
		protected $seqSniffer;
		/** @var SplSniffer */
		protected $assocSniffer;

		protected function setUp() {
			$this->sniffer = SplSniffer::forType('array');
			$this->seqSniffer = SplSniffer::forType('array::seq');
			$this->assocSniffer = SplSniffer::forType('array::assoc');
		}

		public function testStaticCreation() {
			$sniffer = SplSniffer::forType('array');
			$this->assertEquals('Adepto\\SniffArray\\Sniff\\MixedArraySniffer', get_class($sniffer));

			$sniffer = SplSniffer::forType('mixedArray');
			$this->assertEquals('Adepto\\SniffArray\\Sniff\\MixedArraySniffer', get_class($sniffer));

			$sniffer = SplSniffer::forType('array::sequential');
			$this->assertEquals('Adepto\\SniffArray\\Sniff\\MixedArraySniffer', get_class($sniffer));

			$sniffer = SplSniffer::forType('array::seq');
			$this->assertEquals('Adepto\\SniffArray\\Sniff\\MixedArraySniffer', get_class($sniffer));

			$sniffer = SplSniffer::forType('array::associative');
			$this->assertEquals('Adepto\\SniffArray\\Sniff\\MixedArraySniffer', get_class($sniffer));

			$sniffer = SplSniffer::forType('array::assoc');
			$this->assertEquals('Adepto\\SniffArray\\Sniff\\MixedArraySniffer', get_class($sniffer));
		}

		public function testStaticIsAssociative() {
			$this->assertTrue(MixedArraySniffer::isAssociative([
				'key'	=>	'value'
			]));
			$this->assertTrue(MixedArraySniffer::isAssociative([
				'nested'	=>	[
					'someKey'	=>	'someVal',
					'flag'		=>	false,
					'index'		=>	456
				]
			]));
			$this->assertTrue(MixedArraySniffer::isAssociative([]));

			$this->assertFalse(MixedArraySniffer::isAssociative([1, 2, 3]));
			$this->assertFalse(MixedArraySniffer::isAssociative(['one', true, 3]));
		}

		public function testStaticIsSequential() {
			$this->assertTrue(MixedArraySniffer::isSequential([1, 2, 3]));
			$this->assertTrue(MixedArraySniffer::isSequential(['one', true, 3]));
			$this->assertTrue(MixedArraySniffer::isSequential([[1, 2, 3], [4, 5, 6], [7, 8, 9]]));
			$this->assertTrue(MixedArraySniffer::isSequential([]));

			$this->assertFalse(MixedArraySniffer::isSequential([
				'key'	=>	'value'
			]));
			$this->assertFalse(MixedArraySniffer::isSequential([
				'nested'	=>	[
					'someKey'	=>	'someVal',
					'flag'		=>	false,
					'index'		=>	456
				]
			]));
		}

		public function testSniffPositive() {
			$this->assertTrue($this->sniffer->sniff([]));
			$this->assertTrue($this->sniffer->sniff(['a', 'sequential', 'array']));
			$this->assertTrue($this->sniffer->sniff(['string', true, 123]));
			$this->assertTrue($this->sniffer->sniff([
				'key'	=>	'value',
				'flag'	=>	true,
				'count'	=>	42
			]));
		}

		public function testSniffNegative() {
			$this->assertFalse($this->sniffer->sniff(true));
			$this->assertFalse($this->sniffer->sniff(false));
			$this->assertFalse($this->sniffer->sniff(''));
			$this->assertFalse($this->sniffer->sniff('string'));
			$this->assertFalse($this->sniffer->sniff(null));
			$this->assertFalse($this->sniffer->sniff(123));
			$this->assertFalse($this->sniffer->sniff(123.456));
			$this->assertFalse($this->sniffer->sniff(INF));
			$this->assertFalse($this->sniffer->sniff(NAN));
		}

		public function testSniffStrict() {
			$this->assertTrue($this->sniffer->sniff(['a', 'sequential', 'array'], true));
			$this->assertTrue($this->sniffer->sniff(['string', true, 123], true));
			$this->assertTrue($this->sniffer->sniff([
				'key'	=>	'value',
				'flag'	=>	true,
				'count'	=>	42
			], true));

			$this->assertFalse($this->sniffer->sniff([], true));
		}

		public function testSniffColon() {
			$this->assertTrue($this->seqSniffer->sniff(['a', 'sequential', 'array']));
			$this->assertTrue($this->seqSniffer->sniff([]));
			$this->assertTrue($this->seqSniffer->sniff(['string', true, 123], true));

			$this->assertTrue($this->assocSniffer->sniff([
				'key'	=>	'value',
				'flag'	=>	true,
				'count'	=>	42
			]));
			$this->assertTrue($this->assocSniffer->sniff([]));

			$this->assertFalse($this->assocSniffer->sniff(['a', 'sequential', 'array']));
			$this->assertFalse($this->assocSniffer->sniff([], true));
			$this->assertFalse($this->assocSniffer->sniff(['string', true, 123], true));

			$this->assertFalse($this->seqSniffer->sniff([
				'key'	=>	'value',
				'flag'	=>	true,
				'count'	=>	42
			]));
			$this->assertFalse($this->seqSniffer->sniff([], true));
		}
	}
