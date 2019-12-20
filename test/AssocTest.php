<?php
namespace SeanMorris\Bob\Test;
class AssocTest extends \UnitTestCase
{
	public function testSmallNumeric()
	{
		for($i = -2**9; $i < 2**9; $i++)
		{
			$reference = range($i, $i+8);
			$keys      = array_map([get_called_class(), 'stringKey'], $reference);

			$reference = array_combine($keys, $reference);

			$blob      = \SeanMorris\Bob\Bank::encode($reference);

			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob);


			$this->assertEqual(
				count(array_diff($reference, $sample))
				, 0
				, sprintf(
					'Small Numeric Assocs of length %d don\'t match.'
					, $i
				)
			);
		}
	}

	public function testLargeNumeric()
	{
		for($i = -2**16; $i < -2**16 + (2**10); $i++)
		{
			$reference = range($i, $i+8);
			$keys      = array_map([get_called_class(), 'stringKey'], $reference);

			$reference = array_combine($keys, $reference);
			$blob      = \SeanMorris\Bob\Bank::encode($reference);

			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob);

			$this->assertEqual(
				count(array_diff($reference, $sample))
				, 0
				, sprintf(
					'Large Numeric Assocs of length %d don\'t match.'
					, $i
				)
			);
		}

		for($i = 2**16; $i < 2**16 + (2**10); $i++)
		{
			$reference = range($i, $i+8);
			$keys      = array_map([get_called_class(), 'stringKey'], $reference);

			$reference = array_combine($keys, $reference);
			$blob      = \SeanMorris\Bob\Bank::encode($reference);

			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob);

			$this->assertEqual(
				count(array_diff($reference, $sample))
				, 0
				, sprintf(
					'Large Numeric Assocs of length %d don\'t match.'
					, $i
				)
			);
		}
	}

	public function testVeryLargeNumeric()
	{
		for($i = -2**32; $i < -2**32 + (2**10); $i++)
		{
			$reference = range($i, $i+8);
			$keys      = array_map([get_called_class(), 'stringKey'], $reference);

			$reference = array_combine($keys, $reference);
			$blob      = \SeanMorris\Bob\Bank::encode($reference);

			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob);

			$this->assertEqual(
				count(array_diff($reference, $sample))
				, 0
				, sprintf(
					'Very Large Numeric Assocs of length %d don\'t match.'
					, $i
				)
			);
		}

		for($i = 2**32; $i < 2**32 + (2**10); $i++)
		{
			$reference = range($i, $i+8);
			$keys      = array_map([get_called_class(), 'stringKey'], $reference);

			$reference = array_combine($keys, $reference);
			$blob      = \SeanMorris\Bob\Bank::encode($reference);

			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob);

			$this->assertEqual(
				count(array_diff($reference, $sample))
				, 0
				, sprintf(
					'Very Large Numeric Assocs of length %d don\'t match.'
					, $i
				)
			);
		}
	}

	public function testRoundedNumeric()
	{
		foreach(range(-1, 1, 0.1) as $i)
		{
			$reference = range($i, $i+8);
			$reference = array_map(
				function($r)
				{
					return round($r, 1);
				}
				, $reference
			);
			$keys      = array_map([get_called_class(), 'stringKey'], $reference);
			$reference = array_combine($keys, $reference);
			$options   = ['round' => 1];
			$blob      = \SeanMorris\Bob\Bank::encode($reference, $options);

			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob, $options);

			$reference = array_map('strval', $reference);
			$sample    = array_map('strval', $sample);

			$this->assertEqual(
				count(array_diff($reference, $sample))
				, 0
				, sprintf(
					'Rounded Numeric Lists of length %d don\'t match.'
					, $i
				)
			);
		}

		foreach(range(-1, 1, 0.01) as $i)
		{
			$reference = range($i, $i+8);
			$reference = array_map(
				function($r)
				{
					return round($r, 2);
				}
				, $reference
			);
			$keys      = array_map([get_called_class(), 'stringKey'], $reference);
			$reference = array_combine($keys, $reference);
			$options   = ['round' => 2];
			$blob      = \SeanMorris\Bob\Bank::encode($reference, $options);

			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob, $options);

			$reference = array_map('strval', $reference);
			$sample    = array_map('strval', $sample);

			$this->assertEqual(
				count(array_diff($reference, $sample))
				, 0
				, sprintf(
					'Rounded Numeric Lists of length %d don\'t match.'
					, $i
				)
			);
		}

		foreach(range(-1, 1, 0.001) as $i)
		{
			$reference = range($i, $i+8);
			$reference = array_map(
				function($r)
				{
					return round($r, 3);
				}
				, $reference
			);
			$keys      = array_map([get_called_class(), 'stringKey'], $reference);
			$reference = array_combine($keys, $reference);
			$options   = ['round' => 3];
			$blob      = \SeanMorris\Bob\Bank::encode($reference, $options);

			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob, $options);

			$reference = array_map('strval', $reference);
			$sample    = array_map('strval', $sample);

			$this->assertEqual(
				count(array_diff($reference, $sample))
				, 0
				, sprintf(
					'Rounded Numeric Lists of length %d don\'t match.'
					, $i
				)
			);
		}

		foreach(range(-1, 1, 0.0001) as $i)
		{
			$reference = range($i, $i+8);
			$reference = array_map(
				function($r)
				{
					return round($r, 4);
				}
				, $reference
			);
			$keys      = array_map([get_called_class(), 'stringKey'], $reference);
			$reference = array_combine($keys, $reference);
			$options   = ['round' => 4];
			$blob      = \SeanMorris\Bob\Bank::encode($reference, $options);

			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob, $options);

			$reference = array_map('strval', $reference);
			$sample    = array_map('strval', $sample);

			$this->assertEqual(
				count(array_diff($reference, $sample))
				, 0
				, sprintf(
					'Rounded Numeric Lists of length %d don\'t match.'
					, $i
				)
			);
		}
	}

	public function testStrings()
	{
		for($i = 36**3 - 36*2; $i < 36**3 + 36*2; $i++)
		{
			$reference = range($i, $i+8);
			$keys      = array_map([get_called_class(), 'stringKey'], $reference);

			$reference = array_combine($keys, $reference);

			$reference = array_map(
				function($string)
				{
					return base_convert($string,10,36)
						. '!'
						. base_convert($string,10,36);
				}
				, $reference
			);

			$blob      = \SeanMorris\Bob\Bank::encode($reference);
			$sample    = \SeanMorris\Bob\Bank::decode($blob);

			$this->assertEqual(
				count(array_diff($reference, $sample))
				, 0
				, sprintf(
					'Text Assocs of length %d don\'t match.'
					, $i
				)
			);
		}
	}

	function stringKey($string)
	{
		$b36 = sha1($string);

		return '!'. $b36;
	}
}
