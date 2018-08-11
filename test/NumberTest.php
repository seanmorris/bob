<?php
namespace SeanMorris\Bob\Test;
class NumberTest extends \UnitTestCase
{
	public function testSmallNumbers()
	{
		for($i = -2**9; $i < 2**9; $i++)
		{
			$reference = $i;
			$blob      = \SeanMorris\Bob\Bank::encode($reference);
			
			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob);

			$this->assertEqual(
				$reference
				, $sample
				, sprintf(
					'Small numbers %s and %s don\'t match'
					, $reference
					, $sample
				)
			);
		}
	}

	public function testLargeNubers()
	{
		for($i = -2**16; $i < -2**16 + (2**10); $i++)
		{
			$reference = $i;
			$blob      = \SeanMorris\Bob\Bank::encode($reference);
			
			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob);

			$this->assertEqual(
				$reference
				, $sample
				, sprintf(
					'Large numbers %s and %s don\'t match'
					, $reference
					, $sample
				)
			);
		}

		for($i = 2**16; $i < 2**16 + (2**10); $i++)
		{
			$reference = $i;
			$blob      = \SeanMorris\Bob\Bank::encode($reference);
			
			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob);

			$this->assertEqual(
				$reference
				, $sample
				, sprintf(
					'Large numbers %s and %s don\'t match'
					, $reference
					, $sample
				)
			);
		}
	}

	public function testVeryLargeNubers()
	{
		for($i = -2**32; $i < -2**32 + (2**10); $i++)
		{
			$reference = $i;
			$blob      = \SeanMorris\Bob\Bank::encode($reference);
			
			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob);

			$this->assertEqual(
				$reference
				, $sample
				, sprintf(
					'Very Large numbers %s and %s don\'t match'
					, $reference
					, $sample
				)
			);
		}

		for($i = 2**32; $i < 2**32 + (2**10); $i++)
		{
			$reference = $i;
			$blob      = \SeanMorris\Bob\Bank::encode($reference);
			
			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob);

			$this->assertEqual(
				$reference
				, $sample
				, sprintf(
					'Very Large numbers %s and %s don\'t match'
					, $reference
					, $sample
				)
			);
		}
	}
}
