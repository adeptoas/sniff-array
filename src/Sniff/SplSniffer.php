<?php
	namespace Adepto\SniffArray\Sniff;

	use Adepto\SniffArray\Exception\{
		ClassNotFoundException, InvalidValueException
	};

	/**
	 * Class SplSniffer
	 * An abstract base class to sniff / check basic SPL types / primitives in PHP
	 *
	 * @author  suushie_maniac
	 * @version 1.0
	 */
	abstract class SplSniffer {
		const BASE_NAMESPACE = 'Adepto\\SniffArray\\Sniff';

		const TYPE_BOOL = 'bool';
		const TYPE_STRING = 'string';
		const TYPE_INT = 'int';
		const TYPE_NUMBER = 'number';
		const TYPE_NULL = 'null';
		const TYPE_MIXED = 'mixed';
		const TYPE_MIXED_ARRAY = 'mixedArray';
		const TYPE_OBJECT = 'object';

		const SUPPORTED_TYPES = [
			self::TYPE_BOOL,
			self::TYPE_STRING,
			self::TYPE_INT,
			self::TYPE_NUMBER,
			self::TYPE_NULL,
			self::TYPE_MIXED,
			self::TYPE_MIXED_ARRAY,
			self::TYPE_OBJECT
		];

		const TYPE_REMAPPINGS = [
			'boolean'	=>	self::TYPE_BOOL,
			'empty'		=>	self::TYPE_NULL,
			'numeric'	=>	self::TYPE_NUMBER,
			'integer'	=>	self::TYPE_INT,
			'any'		=>	self::TYPE_MIXED,
			'array'		=>	self::TYPE_MIXED_ARRAY,
			'class'		=>	self::TYPE_OBJECT
		];

		const COLON_SPECIFIER = '::';

		/**
		 * Factory method to obtain a subclass of SplSniffer that sniffs/checks for $type
		 *
		 * @param string $type The desired $type to check for
		 * @param bool $throw Whether an exception should be raised on failure
		 *
		 * @throws ClassNotFoundException If no subclass exists for the given $type
		 *
		 * @return SplSniffer The sniffer sniffing/checking elements of $type
		 */
		public static function forType(string $type, bool $throw = false): SplSniffer {
			$typeSpecs = explode(self::COLON_SPECIFIER, $type);
			$type = array_shift($typeSpecs);

			$type = static::TYPE_REMAPPINGS[$type] ?? $type;

			$classType = StringSniffer::capitalize($type);
			$snifferClass = self::BASE_NAMESPACE . '\\' . $classType . 'Sniffer';

			if (class_exists($snifferClass)) {
				return new $snifferClass($throw, $typeSpecs);
			} else {
				throw new ClassNotFoundException($snifferClass . ' for type ' . $type . ' not found');
			}
		}

		protected $throw;
		protected $specData;

		protected function __construct(bool $throw = false, array $specData = []) {
			$this->throw = $throw;
			$this->specData = $specData;
		}

		/**
		 * Sniff / check $val for conformity to the type determined by this instances class
		 *
		 * @param mixed $val The value to check for
		 * @param bool $isStrict Whether the strict mode is enabled for matching
		 *
		 * @return bool If $val matches or not
		 *
		 * @throws InvalidValueException If $val does not match and $throw of this instance is true
		 */
		public function sniff($val, bool $isStrict = false): bool {
			$accept = $this->sniffVal($val, $isStrict);

			if ($this->throw && !$accept) {
				throw new InvalidValueException(var_export($val, true) . ' does not confirm to type specifications');
			}

			$colonAccept = count($this->specData) <= 0;

			if ($accept) {
				foreach ($this->specData as $colonData) {
					$colonAccept |= $this->sniffColonVal($val, $colonData);
				}
			}

			if ($this->throw && !$colonAccept) {
				throw new InvalidValueException(var_export($val, true) . ' does not confirm to type specification colon data!');
			}

			return $accept && $colonAccept;
		}

		protected abstract function sniffVal($val, bool $isStrict = false): bool;

		protected function sniffColonVal($val, string $colonData): bool {
			return true;
		}

		/**
		 * Check if all types in $types have valid SplSniffers
		 *
		 * @param array $types A sequential list of types to check for
		 *
		 * @return bool True iff all types match
		 */
		public static function validateTypes(array $types): bool {
			$valid = true;

			foreach ($types as $type) {
				if (!is_string($type)) {
					return false;
				}

				$valid &= self::isValidType($type);
			}

			return $valid;
		}

		/**
		 * Check if $type has a valid SplSniffer
		 *
		 * @param string $type The type to check for
		 *
		 * @return bool True iff an SplSniffer exists
		 */
		public static function isValidType(string $type): bool {
			$type = explode(static::COLON_SPECIFIER, $type)[0];
			$type = static::TYPE_REMAPPINGS[$type] ?? $type;
			return in_array($type, static::SUPPORTED_TYPES);
		}

		/**
		 * Get a list of all known type definitions including all supported aliases
		 *
		 * @return array The unique list of type strings
		 */
		public static function getSupportedTypes(): array {
			return array_unique(array_merge(static::SUPPORTED_TYPES, array_keys(static::TYPE_REMAPPINGS)));
		}
	}