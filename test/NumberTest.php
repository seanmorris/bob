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

	public function testRoundedNubers()
	{
		foreach(range(-1, 1, 0.1) as $i)
		{
			$reference = round($i, 1);
			$options   = ['round' => 1];
			$blob      = \SeanMorris\Bob\Bank::encode($reference, $options);
			
			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob, $options);

			$this->assertEqual(
				(string) $reference
				, (string) $sample
				, sprintf(
					'Rounded Numbers %s and %s don\'t match'
					, $reference
					, $sample
				)
			);
		}

		foreach(range(-1, 1, 0.01) as $i)
		{
			$reference = round($i, 2);
			$options   = ['round' => 2];
			$blob      = \SeanMorris\Bob\Bank::encode($reference, $options);
			
			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob, $options);

			$this->assertEqual(
				(string) $reference
				, (string) $sample
				, sprintf(
					'Rounded Numbers %s and %s don\'t match'
					, $reference
					, $sample
				)
			);
		}

		foreach(range(-1, 1, 0.001) as $i)
		{
			$reference = round($i, 3);
			$options   = ['round' => 3];
			$blob      = \SeanMorris\Bob\Bank::encode($reference, $options);
			
			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob, $options);

			$this->assertEqual(
				(string) $reference
				, (string) $sample
				, sprintf(
					'Rounded Numbers %s and %s don\'t match'
					, $reference
					, $sample
				)
			);
		}

		foreach(range(-1, 1, 0.0001) as $i)
		{
			$reference = round($i, 4);
			$options   = ['round' => 4];
			$blob      = \SeanMorris\Bob\Bank::encode($reference, $options);
			
			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob, $options);

			$this->assertEqual(
				(string) $reference
				, (string) $sample
				, sprintf(
					'Rounded Numbers %s and %s don\'t match'
					, $reference
					, $sample
				)
			);
		}
	}
}
