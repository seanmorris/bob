<?php
namespace SeanMorris\Bob\Test;
class TextTest extends \UnitTestCase
{
	public function testStrings()
	{
		for($i = 36**2; $i < 36**3 + 36*1; $i++)
		{
			$reference = 'String' . base_convert($i,10,36);

			$blob      = \SeanMorris\Bob\Bank::encode($reference);

			// dumpBytes($blob);

			$sample    = \SeanMorris\Bob\Bank::decode($blob);
			
			$this->assertEqual(
				$reference
				, $sample
				, sprintf(
					'Numeric lists of length %d don\'t match.'
					, $i
				)
			);
		}
	}
}