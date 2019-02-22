<?php
namespace SeanMorris\Bob;
class Bank
{
	const NULL      = 0x0C
		, BYTE      = 0x01
		, SHORT     = 0x02
		, LONG      = 0x04
		, FPOINT    = 0x18
		, TEXT      = 0x08
		, LIST      = 0x10
		, REFERENCE = 0x20

		, UNSIGNED  = 0x40
		, SIGNED    = 0x80

		, FPSINGLE  = 0x40
		, FPDOUBLE  = 0x80

		, SIMPLE    = 0x40
		, COMPLEX   = 0x80
		, TYPED     = 0xC0
	;

	protected $bank = [], $stack = [], $blob, $hashes = [];

	public function __construct($value = NULL)
	{
		ini_set('memory_limit',       '4G');
		ini_set('max_execution_time', '-1');
		$this->bank($value);
	}

	protected function bank(...$values)
	{
		foreach($values as $value)
		{
			$this->bank[] = $value;
		}
	}

	public static function encode($value, $options = [])
	{
		$static = new \SeanMorris\Bob\Bank($value);

		return $static->encodeBank($options);
	}

	public function encodeBank($options)
	{
		$i = 0;
		$blobs = [];

		while($i < count($this->bank))
		{
			$value = $this->bank[$i];

			$blobs[] = static::encodeSingle($value, $options);

			$i++;
		}

		return implode(NULL, $blobs);
	}

	public function encodeSingle($value, $options)
	{
		switch(static::type($value, $options))
		{
			case chr(static::NULL):
				return static::encodeNull($value, $options);
				break;

			case chr(static::BYTE | static::UNSIGNED):
				return static::encodeUnsignedByte($value, $options);
				break;

			case chr(static::SHORT | static::UNSIGNED):
				return static::encodeUnsignedShort($value, $options);
				break;

			case chr(static::LONG | static::UNSIGNED):
				return static::encodeUnsignedLong($value, $options);
				break;

			case chr(static::BYTE | static::SIGNED):
				return static::encodeSignedByte($value, $options);
				break;

			case chr(static::SHORT | static::SIGNED):
				return static::encodeSignedShort($value, $options);
				break;

			case chr(static::LONG | static::SIGNED):
				return static::encodeSignedLong($value, $options);
				break;

			case chr(static::FPOINT):
				return static::encodeFloatingPoint($value, $options);
				break;

			case chr(static::TEXT):
				return static::encodeText($value, $options);
				break;

			case chr(static::LIST | static::SIMPLE):
				return static::encodeSimpleList($value, $options);
				break;

			case chr(static::LIST | static::COMPLEX):
				return static::encodeComplexList($value, $options);
				break;

			case chr(static::LIST | static::TYPED):
				return static::encodeTypedList($value, $options);
				break;

			case chr(static::REFERENCE):
				throw new Exception('Cannot encode reference');
				break;
		}
	}

	protected function encodeNull($value, $options)
	{
		return chr(static::NULL);
	}

	protected function encodeUnsignedByte($value, $options)
	{
		return chr(static::BYTE | static::UNSIGNED)
			. pack('C', $value);
	}

	protected function encodeUnsignedShort($value, $options)
	{
		return chr(static::SHORT | static::UNSIGNED)
			. pack('S', $value);
	}

	protected function encodeUnsignedLong($value, $options)
	{
		return chr(static::LONG | static::UNSIGNED)
			. pack('V', $value);
	}

	protected function encodeSignedByte($value, $options)
	{
		return chr(static::BYTE | static::SIGNED)
			. pack('c', $value);
	}

	protected function encodeSignedShort($value, $options)
	{
		return chr(static::SHORT | static::SIGNED)
			. pack('s', $value);
	}

	protected function encodeSignedLong($value, $options)
	{
		return chr(static::LONG | static::SIGNED)
			. pack('l', $value);
	}

	protected function encodeFloatingPoint($value, $options)
	{
		$reference = $value;
		$encoded   = pack('g', $value);
		$decoded   = unpack('g', pack('g', $value))[1];

		if($options['round'] ?? FALSE)
		{
			$reference = round($reference, $options['round']);
			$decoded   = round($decoded,   $options['round']);
		}

		if($reference === $decoded)
		{
			return chr(static::FPOINT | static::FPSINGLE)
				. pack('g', $reference);
		}

		return chr(static::FPOINT | static::FPDOUBLE)
			. pack('e', $reference);
	}

	protected function encodeText($value, $options)
	{
		return chr(static::TEXT)
			. pack('a*', $value)
			. chr(0x00);
	}

	protected function encodeSimpleList($value, $options)
	{
		return chr(static::LIST | static::SIMPLE)
			. static::encodeList($value, $options);
	}

	protected function encodeComplexList($value, $options)
	{
		return chr(static::LIST | static::COMPLEX)
			. static::encodeList($value, $options);
	}

	protected function encodeTypedList($value, $options)
	{
		return chr(static::LIST | static::TYPED)
			. static::encodeText(get_class($value), $options)
			. static::encodeList($value, $options);
	}

	protected function encodeReference($value, $options)
	{
		if(!is_object($value))
		{
			$pointer = count($this->bank);

			$found = false;

			foreach($this->bank as $i => $object)
			{
				if($object === $value)
				{
					$pointer = $i;
					$found = true;
				}
			}

			$this->bank[] = $value;
			return;			
		}

		if(array_key_exists(spl_object_hash($value), $this->hashes))
		{
			$pointer = $this->hashes[$i];
		}
		else
		{
			$pointer = count($this->bank);
			$this->hashes[spl_object_hash($value)] = $pointer;
			$this->bank[] = $value;
		}

		return chr(static::REFERENCE)
			. pack('V', $pointer);
	}

	protected function encodeList($value, $options)
	{
		$content = '';

		foreach($value as $i => $v)
		{
			if(static::isntNumeric($i) || is_object($value))
			{
				$content .= static::encodeSingle($i, $options);
			}

			if(!is_scalar($v))
			{
				$content .= $this->encodeReference($v, $options);
				continue;
			}

			$content .= static::encodeSingle($v, $options);
		}

		return $content . chr(0x00);
	}

	protected function type($value, $options)
	{
		switch(TRUE)
		{
			case is_object($value):
				return chr(static::LIST | static::TYPED);
				break;

			case is_array($value)
				&& $value
				&& array_filter(
					array_keys($value)
					, [static::class, 'isntNumeric']
				):

				return chr(static::LIST | static::COMPLEX);
				break;

			case is_array($value):
				return chr(static::LIST | static::SIMPLE);
				break;

			case is_string($value) && static::isntNumeric($value):
				return chr(static::TEXT);
				break;
		}

		if(is_null($value))
		{
			return chr(static::NULL);
		}

		switch(TRUE)
		{
			case $value >= 2**32 || is_float($value):
				return chr(static::FPOINT);
				break;

			case $value >= 2**16:
				return chr(static::LONG | static::UNSIGNED);
				break;

			case $value >= 2**8:
				return chr(static::SHORT | static::UNSIGNED);
				break;

			case $value >= 0:
				return chr(static::BYTE | static::UNSIGNED);
				break;

			case $value < -2**31:
				return chr(static::FPOINT);
				break;

			case $value < -2**15:
				return chr(static::LONG | static::SIGNED);
				break;

			case $value < -2**7:
				return chr(static::SHORT | static::SIGNED);
				break;

			case $value < 0:
				return chr(static::BYTE | static::SIGNED);
				break;
		}

		throw new Exception('Cannot encode value: ' . print_r($value, 1));
	}

	protected static function isntNumeric($value)
	{
		return !is_numeric($value);
	}

	protected function decodeNull($value, $options)
	{
		return [NULL, 1];
	}

	protected function decodeUnsignedByte($value, $options)
	{
		return [unpack('C', $value)[1], 1];
	}

	protected function decodeUnsignedShort($value, $options)
	{
		return [unpack('S', $value)[1], 2];
	}

	protected function decodeUnsignedLong($value, $options)
	{
		return [unpack('V', $value)[1], 4];
	}

	protected function decodeSignedByte($value, $options)
	{
		return [unpack('c', $value)[1], 1];
	}

	protected function decodeSignedShort($value, $options)
	{
		return [unpack('s', $value)[1], 2];
	}

	protected function decodeSignedLong($value, $options)
	{
		return [unpack('l', $value)[1], 4];
	}

	protected function decodeFloatingPointSingle($value, $options)
	{
		$decoded = unpack('g', $value)[1];

		if($options['round'] ?? FALSE)
		{
			$decoded = round($decoded, $options['round']);
		}

		return [$decoded, 4];
	}

	protected function decodeFloatingPointDouble($value, $options)
	{
		return [unpack('e', $value)[1], 8];

		// $decoded = unpack('e', $value)[1];

		// if($options['round'] ?? FALSE)
		// {
		// 	$decoded = round($decoded, $options['round']);
		// }

		// return [$decoded, 4];
	}

	protected function decodeText($blob, $options)
	{
		$bytes = '';

		for($i = 0; $i < strlen($blob); $i++)
		{
			if($blob[$i] == chr(0x00))
			{
				break;
			}

			$bytes .= $blob[$i];
		}

		$text = unpack('a*', $bytes)[1];

		return [$text, strlen($text)];
	}

	protected function decodeReference($value, $options)
	{
		return [unpack('V', $value)[1], 4];
	}

	protected function decodeSimpleList($blob, $options)
	{
		$length = 0;
		$bank   = [];

		for($i = 0; $i < strlen($blob); NULL)
		{
			$byte = $blob[$i];

			switch($byte)
			{
				case chr(0x00):
					break 2;
				break;

				case chr(static::NULL):
					list($bank[], $length) = static::decodeNull($byte, $options);
					$i ++;
				break;

				case chr(static::BYTE | static::UNSIGNED):
					$bytes = substr($blob, $i+1);

					list($bank[], $length) = static::decodeUnsignedByte($bytes, $options);
					$i += $length;
					$i ++;
					break;

				case chr(static::SHORT | static::UNSIGNED):
					$bytes = substr($blob, $i+1);

					list($bank[], $length) = static::decodeUnsignedShort($bytes, $options);
					$i += $length;
					$i ++;
					break;

				case chr(static::LONG | static::UNSIGNED):
					$bytes = substr($blob, $i+1);

					list($bank[], $length) = static::decodeUnsignedLong($bytes, $options);
					$i += $length;
					$i ++;
					break;

				case chr(static::BYTE | static::SIGNED):
					$bytes = substr($blob, $i+1);

					list($bank[], $length) = static::decodeSignedByte($bytes, $options);
					$i += $length;
					$i ++;
					break;

				case chr(static::SHORT | static::SIGNED):
					$bytes = substr($blob, $i+1);

					list($bank[], $length) = static::decodeSignedShort($bytes, $options);
					$i += $length;
					$i ++;
					break;

				case chr(static::LONG | static::SIGNED):
					$bytes = substr($blob, $i+1);

					list($bank[], $length) = static::decodeSignedLong($bytes, $options);
					$i += $length;
					$i ++;
					break;

				case chr(static::FPOINT | static::FPSINGLE):
					$bytes = substr($blob, $i+1);

					list($bank[], $length) = static::decodeFloatingPointSingle($bytes, $options);
					$i += $length;
					$i ++;
					break;

				case chr(static::FPOINT | static::FPDOUBLE):
					$bytes = substr($blob, $i+1);

					list($bank[], $length) = static::decodeFloatingPointDouble($bytes, $options);
					$i += $length;
					$i ++;
					break;

				case chr(static::TEXT):
					$bytes = substr($blob, $i+1);

					list($bank[], $length) = static::decodeText($bytes, $options);
					$i += $length;
					$i ++;
					$i ++;
					break;

				case chr(static::REFERENCE):
					$bytes = substr($blob, $i+1);

					list($pointer, $length) = static::decodeReference($bytes, $options);

					$bank[] = $pointer;

					$slot =& $bank[ count($bank) - 1 ];

					$stacker = function(&$slot) use($pointer) {
						return function() use(&$slot, $pointer) {
							$slot = $this->bank[$pointer];
						};
					};

					$this->stack[] = $stacker($slot);

					$slot = $pointer;

					$i += $length;
					$i ++;
					break;

				default:
					$i++;
			}
		}

		return [$bank, $i];
	}

	protected function decodeComplexList($blob, $options)
	{
		list($list, $length) = static::decodeSimpleList($blob, $options);

		$keys = array_filter(
			$list
			, function ($key) {
				return !($key % 2);
			}
			, ARRAY_FILTER_USE_KEY
		);

		$values = array_filter(
			$list
			, function ($key) {
				return ($key % 2);
			}
			, ARRAY_FILTER_USE_KEY
		);

		return [array_combine($keys, $values), $length];
	}

	protected function decodeTypedList($blob, $options)
	{
		$typeBlob = substr($blob, 1);

		list($type, $typeLength) = static::decodeText($typeBlob, $options);

		$listBlob = substr($blob, $typeLength + 2);

		list($list, $listLength) = static::decodeComplexList($listBlob, $options);

		$object = new $type;

		foreach($list as $i => &$v)
		{
			$object->{$i} =& $v;
		}

		return [$object, $typeLength + $listLength + 3];
	}

	public function decodeBlob($blob, $options)
	{
		$this->bank = [];

		for($i = 0; $i < strlen($blob); NULL)
		{
			$byte = $blob[$i];

			switch($byte)
			{
				case chr(static::NULL):
					list($this->bank[], $length) = static::decodeNull($byte, $options);
					$i ++;
					break;

				case chr(static::BYTE | static::UNSIGNED):
					$bytes = substr($blob, $i+1);

					list($this->bank[], $length) = static::decodeUnsignedByte($bytes, $options);
					$i += $length;
					$i ++;
					break;

				case chr(static::SHORT | static::UNSIGNED):
					$bytes = substr($blob, $i+1);

					list($this->bank[], $length) = static::decodeUnsignedShort($bytes, $options);
					$i += $length;
					$i ++;
					break;

				case chr(static::LONG | static::UNSIGNED):
					$bytes = substr($blob, $i+1);

					list($this->bank[], $length) = static::decodeUnsignedLong($bytes, $options);
					$i += $length;
					$i ++;
					break;

				case chr(static::BYTE | static::SIGNED):
					$bytes = substr($blob, $i+1);

					list($this->bank[], $length) = static::decodeSignedByte($bytes, $options);
					$i += $length;
					$i ++;
					break;

				case chr(static::SHORT | static::SIGNED):
					$bytes = substr($blob, $i+1);

					list($this->bank[], $length) = static::decodeSignedShort($bytes, $options);
					$i += $length;
					$i ++;
					break;

				case chr(static::LONG | static::SIGNED):
					$bytes = substr($blob, $i+1);

					list($this->bank[], $length) = static::decodeSignedLong($bytes, $options);
					$i += $length;
					$i ++;
					break;

				case chr(static::FPOINT | static::FPSINGLE):
					$bytes = substr($blob, $i+1);

					list($this->bank[], $length) = static::decodeFloatingPointSingle($bytes, $options);
					$i += $length;
					$i ++;
					break;

				case chr(static::FPOINT | static::FPDOUBLE):
					$bytes = substr($blob, $i+1);

					list($this->bank[], $length) = static::decodeFloatingPointDouble($bytes, $options);
					$i += $length;
					$i ++;
					break;

				case chr(static::TEXT):
					$bytes = substr($blob, $i+1);

					list($this->bank[], $length) = static::decodeText($bytes, $options);
					$i += $length;
					$i ++;
					$i ++;
					break;

				case chr(static::LIST | static::SIMPLE):
					$bytes = substr($blob, $i+1);

					list($this->bank[], $length) = static::decodeSimpleList($bytes, $options);
					$i += $length;
					$i ++;
					break;

				case chr(static::LIST | static::COMPLEX):
					$bytes = substr($blob, $i+1);

					list($this->bank[], $length) = static::decodeComplexList($bytes, $options);
					$i += $length;
					$i ++;
					break;

				case chr(static::LIST | static::TYPED):
					$bytes = substr($blob, $i+1);

					list($this->bank[], $length) = static::decodeTypedList($bytes, $options);
					$i += $length;
					$i ++;
					break;

				default:
					$i++;
			}
		}

		foreach($this->stack as $post)
		{
			$post();
		}

		return $this->bank[0];
	}

	public static function decode($blob, $options = [])
	{
		$static = new static();

		return $static->decodeBlob($blob, $options);
	}
}
