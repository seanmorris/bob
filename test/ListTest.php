<?php
namespace SeanMorris\Bob\Test;
class ListTest extends \UnitTestCase
{
	public function testSmallNumeric()
	{
		for($i = -2**9; $i < 2**9; $i++)
		{
			$reference = range($i, $i+8);
			$blob      = \SeanMorris\Bob\Bank::encode($reference);
			
			// dumpBytes($blob);
			
			$sample    = \SeanMorris\Bob\Bank::decode($blob);


			$this->assertEqual(
				count(array_diff($reference, $sample))
				, 0
				, sprintf(
					'Numeric Lists of length %d don\'t match.'
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
			$blob      = \SeanMorris\Bob\Bank::encode($reference);
			
			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob);

			$this->assertEqual(
				count(array_diff($reference, $sample))
				, 0
				, sprintf(
					'Large Numeric Lists of length %d don\'t match.'
					, $i
				)
			);
		}

		for($i = 2**16; $i < 2**16 + (2**10); $i++)
		{
			$reference = range($i, $i+8);
			$blob      = \SeanMorris\Bob\Bank::encode($reference);
			
			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob);

			$this->assertEqual(
				count(array_diff($reference, $sample))
				, 0
				, sprintf(
					'Large Numeric Lists of length %d don\'t match.'
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
			$blob      = \SeanMorris\Bob\Bank::encode($reference);
			
			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob);

			$this->assertEqual(
				count(array_diff($reference, $sample))
				, 0
				, sprintf(
					'Very Large Numeric Lists of length %d don\'t match.'
					, $i
				)
			);
		}

		for($i = 2**32; $i < 2**32 + (2**10); $i++)
		{
			$reference = range($i, $i+8);
			$blob      = \SeanMorris\Bob\Bank::encode($reference);
			
			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob);

			$this->assertEqual(
				count(array_diff($reference, $sample))
				, 0
				, sprintf(
					'Very Large Numeric Lists of length %d don\'t match.'
					, $i
				)
			);
		}
	}

	public function testStrings()
	{
		for($i = 36**3 - 36*2; $i < 36**3 + 36*2; $i++)
		{
			$reference = array_map(
				function($string)
				{
					return base_convert($string,10,36)
						. '!'
						. base_convert($string,10,36);
				}
				, range($i, $i+32)
			);

			$blob      = \SeanMorris\Bob\Bank::encode($reference);
			
			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob);

			$this->assertEqual(
				count(array_diff($reference, $sample))
				, 0
				, sprintf(
					'Numeric lists of length %d don\'t match.'
					, $i
				)
			);
		}
	}
}
