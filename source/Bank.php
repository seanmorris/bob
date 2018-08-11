<?php
namespace SeanMorris\Bob;
class Bank
{
	const NULL      = 0x0C
		, BYTE      = 0x01
		, SHORT     = 0x02
		, LONG      = 0x04
		, FPOINT    = 0x04
		, TEXT      = 0x08
		, LIST      = 0x10
		, REFERENCE = 0x20

		, UNSIGNED  = 0x40
		, SIGNED    = 0x80
		
		, SIMPLE    = 0x40
		, COMPLEX   = 0x80
		, TYPED     = 0xC0
	;

	protected $bank = [], $stack = [], $blob;

	public function __construct($value = NULL)
	{
		$this->bank($value);
	}

	protected function bank(...$values)
	{
		foreach($values as $value)
		{
			$this->bank[] = $value;
		}
	}

	public static function encode($value)
	{
		$static = new \SeanMorris\Bob\Bank($value);

		return $static->encodeBank();
	}

	public function encodeBank()
	{
		$i = 0;
		$blobs = [];

		while($i < count($this->bank))
		{
			$value = $this->bank[$i];

			$blobs[] = static::encodeSingle($value);

			$i++;
		}

		return implode(NULL, $blobs);
	}
	public function encodeSingle($value)
	{
		switch(static::type($value))
		{
			case chr(static::NULL):
				return static::encodeNull($value);
				break;

			case chr(static::BYTE | static::UNSIGNED):
				return static::encodeUnsignedByte($value);
				break;

			case chr(static::SHORT | static::UNSIGNED):
				return static::encodeUnsignedShort($value);
				break;

			case chr(static::LONG | static::UNSIGNED):
				return static::encodeUnsignedLong($value);
				break;

			case chr(static::BYTE | static::SIGNED):
				return static::encodeSignedByte($value);
				break;

			case chr(static::SHORT | static::SIGNED):
				return static::encodeSignedShort($value);
				break;

			case chr(static::LONG | static::SIGNED):
				return static::encodeSignedLong($value);
				break;

			case chr(static::FPOINT):
				return static::encodeFloatingPoint($value);
				break;

			case chr(static::TEXT):
				return static::encodeText($value);
				break;

			case chr(static::LIST | static::SIMPLE):
				return static::encodeSimpleList($value);
				break;

			case chr(static::LIST | static::COMPLEX):
				return static::encodeComplexList($value);
				break;

			case chr(static::LIST | static::TYPED):
				return static::encodeTypedList($value);
				break;

			case chr(static::REFERENCE):
				throw new Exception('Cannot encode reference');
				break;
		}
	}

	protected function encodeNull($value)
	{
		return chr(static::NULL);
	}

	protected function encodeUnsignedByte($value)
	{
		return chr(static::BYTE | static::UNSIGNED)
			. pack('C', $value);
	}

	protected function encodeUnsignedShort($value)
	{
		return chr(static::SHORT | static::UNSIGNED)
			. pack('S', $value);
	}

	protected function encodeUnsignedLong($value)
	{
		return chr(static::LONG | static::UNSIGNED)
			. pack('N', $value);
	}

	protected function encodeSignedByte($value)
	{
		return chr(static::BYTE | static::SIGNED)
			. pack('c', $value);
	}

	protected function encodeSignedShort($value)
	{
		return chr(static::SHORT | static::SIGNED)
			. pack('s', $value);
	}

	protected function encodeSignedLong($value)
	{
		return chr(static::LONG | static::SIGNED)
			. pack('l', $value);
	}

	protected function encodeFloatingPoint($value)
	{
		return chr(static::FPOINT)
			. pack('e', $value);
	}

	protected function encodeText($value)
	{
		return chr(static::TEXT)
			. pack('a*', $value)
			. chr(0x00);
	}

	protected function encodeSimpleList($value)
	{
		return chr(static::LIST | static::SIMPLE)
			. static::encodeList($value);
	}

	protected function encodeComplexList($value)
	{
		return chr(static::LIST | static::COMPLEX)
			. static::encodeList($value);
	}

	protected function encodeTypedList($value)
	{
		return chr(static::LIST | static::TYPED)
			. static::encodeText(get_class($value))
			. static::encodeList($value);
	}

	protected function encodeReference($value)
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

		if(!$found)
		{
			$this->bank[] = $value;
		}

		return chr(static::REFERENCE)
			. pack('N', $pointer);
	}

	protected function encodeList($value)
	{
		$content = '';

		foreach($value as $i => $v)
		{
			if(static::isntNumeric($i) || is_object($value))
			{
				$content .= static::encodeSingle($i);
			}

			if(!is_scalar($v))
			{
				$content .= $this->encodeReference($v);
				continue;
			}

			$content .= static::encodeSingle($v);
		}

		return $content . chr(0x00);
	}

	protected function types(...$args)
	{
		return array_map(
			function($arg)
			{
				return static::type($arg);
			}
			, $args
		);
	}

	protected function type($value)
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
			case $value >= 2**32:
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

	protected function decodeNull($value)
	{
		return [NULL, 1];
	}

	protected function decodeUnsignedByte($value)
	{
		return [unpack('C', $value)[1], 1];
	}

	protected function decodeUnsignedShort($value)
	{
		return [unpack('S', $value)[1], 2];
	}

	protected function decodeUnsignedLong($value)
	{
		return [unpack('N', $value)[1], 4];
	}

	protected function decodeSignedByte($value)
	{
		return [unpack('c', $value)[1], 1];
	}

	protected function decodeSignedShort($value)
	{
		return [unpack('s', $value)[1], 2];
	}

	protected function decodeSignedLong($value)
	{
		return [unpack('l', $value)[1], 4];
	}

	protected function decodeFloatingPoint($value)
	{
		return [unpack('e', $value)[1], 8];
	}

	protected function decodeText($blob)
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

	protected function decodeReference($value)
	{
		return [unpack('N', $value)[1], 4];
	}

	protected function decodeSimpleList($blob)
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
					list($bank[], $length) = static::decodeNull($byte);
					$i ++;
				break;

				case chr(static::BYTE | static::UNSIGNED):
					$bytes = substr($blob, $i+1);
					
					list($bank[], $length) = static::decodeUnsignedByte($bytes);
					$i += $length;
					$i ++;
					break;

				case chr(static::SHORT | static::UNSIGNED):
					$bytes = substr($blob, $i+1);
					
					list($bank[], $length) = static::decodeUnsignedShort($bytes);
					$i += $length;
					$i ++;
					break;

				case chr(static::LONG | static::UNSIGNED):
					$bytes = substr($blob, $i+1);
					
					list($bank[], $length) = static::decodeUnsignedLong($bytes);
					$i += $length;
					$i ++;
					break;

				case chr(static::BYTE | static::SIGNED):
					$bytes = substr($blob, $i+1);
					
					list($bank[], $length) = static::decodeSignedByte($bytes);
					$i += $length;
					$i ++;
					break;

				case chr(static::SHORT | static::SIGNED):
					$bytes = substr($blob, $i+1);
					
					list($bank[], $length) = static::decodeSignedShort($bytes);
					$i += $length;
					$i ++;
					break;

				case chr(static::LONG | static::SIGNED):
					$bytes = substr($blob, $i+1);
					
					list($bank[], $length) = static::decodeSignedLong($bytes);
					$i += $length;
					$i ++;
					break;

				case chr(static::FPOINT):
					$bytes = substr($blob, $i+1);
					
					list($bank[], $length) = static::decodeFloatingPoint($bytes);
					$i += $length;
					$i ++;
					break;

				case chr(static::TEXT):
					$bytes = substr($blob, $i+1);

					list($bank[], $length) = static::decodeText($bytes);
					$i += $length;
					$i ++;
					$i ++;
					break;

				case chr(static::REFERENCE):
					$bytes = substr($blob, $i+1);

					list($pointer, $length) = static::decodeReference($bytes);

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

	protected function decodeComplexList($blob)
	{
		[$list, $length] = static::decodeSimpleList($blob);

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

	protected function decodeTypedList($blob)
	{
		$typeBlob = substr($blob, 1);

		[$type, $typeLength] = static::decodeText($typeBlob);

		$listBlob = substr($blob, $typeLength + 2);

		[$list, $listLength] = static::decodeComplexList($listBlob);

		$object = new $type;

		foreach($list as $i => &$v)
		{
			$object->{$i} =& $v;
		}

		return [$object, $typeLength + $listLength + 3];
	}

	public function decodeBlob($blob)
	{
		$this->bank = [];

		for($i = 0; $i < strlen($blob); NULL)
		{
			$byte = $blob[$i];

			switch($byte)
			{
				case chr(static::NULL):
					list($this->bank[], $length) = static::decodeNull($byte);
					$i ++;
					break;

				case chr(static::BYTE | static::UNSIGNED):
					$bytes = substr($blob, $i+1);
					
					list($this->bank[], $length) = static::decodeUnsignedByte($bytes);
					$i += $length;
					$i ++;
					break;

				case chr(static::SHORT | static::UNSIGNED):
					$bytes = substr($blob, $i+1);
					
					list($this->bank[], $length) = static::decodeUnsignedShort($bytes);
					$i += $length;
					$i ++;
					break;

				case chr(static::LONG | static::UNSIGNED):
					$bytes = substr($blob, $i+1);
					
					list($this->bank[], $length) = static::decodeUnsignedLong($bytes);
					$i += $length;
					$i ++;
					break;

				case chr(static::BYTE | static::SIGNED):
					$bytes = substr($blob, $i+1);
					
					list($this->bank[], $length) = static::decodeSignedByte($bytes);
					$i += $length;
					$i ++;
					break;

				case chr(static::SHORT | static::SIGNED):
					$bytes = substr($blob, $i+1);
					
					list($this->bank[], $length) = static::decodeSignedShort($bytes);
					$i += $length;
					$i ++;
					break;

				case chr(static::LONG | static::SIGNED):
					$bytes = substr($blob, $i+1);
					
					list($this->bank[], $length) = static::decodeSignedLong($bytes);
					$i += $length;
					$i ++;
					break;

				case chr(static::FPOINT):
					$bytes = substr($blob, $i+1);
					
					list($this->bank[], $length) = static::decodeFloatingPoint($bytes);
					$i += $length;
					$i ++;
					break;

				case chr(static::TEXT):
					$bytes = substr($blob, $i+1);

					list($this->bank[], $length) = static::decodeText($bytes);
					$i += $length;
					$i ++;
					$i ++;
					break;

				case chr(static::LIST | static::SIMPLE):
					$bytes = substr($blob, $i+1);

					list($this->bank[], $length) = static::decodeSimpleList($bytes);
					$i += $length;
					$i ++;
					break;

				case chr(static::LIST | static::COMPLEX):
					$bytes = substr($blob, $i+1);

					list($this->bank[], $length) = static::decodeComplexList($bytes);
					$i += $length;
					$i ++;
					break;

				case chr(static::LIST | static::TYPED):
					$bytes = substr($blob, $i+1);

					list($this->bank[], $length) = static::decodeTypedList($bytes);
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

	public static function decode($blob)
	{
		$static = new static();

		return $static->decodeBlob($blob);
	}
}
