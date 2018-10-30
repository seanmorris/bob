<?php
namespace SeanMorris\Bob\Test;
class ObjectTest extends \UnitTestCase
{
	public function testFlat()
	{
		for($i = 0; $i < 256; $i++)
		{
			$reference = (object) range($i, $i+8);

			$blob      = \SeanMorris\Bob\Bank::encode($reference);
			
			$sample    = \SeanMorris\Bob\Bank::decode($blob);

			$this->assertTrue(
				hash_equals(
					sha1(print_r($reference,1))
					, sha1(print_r($sample,1))
				)
				, sprintf(
					"Flat objects don't match.\n%s\n%s\n"
					, print_r($reference,1)
					, print_r($sample,1)
				)
			);

			$prev = $reference;
		}
	}

	public function testNested()
	{
		$prev = NULL;

		for($i = 0; $i < 256; $i++)
		{
			$reference = (object) range($i, $i+8);
			$reference->prev = $prev;
			$blob      = \SeanMorris\Bob\Bank::encode($reference);
			
			$sample    = \SeanMorris\Bob\Bank::decode($blob);

			$this->assertTrue(
				hash_equals(
					sha1(print_r($reference,1))
					, sha1(print_r($sample,1))
				)
				, sprintf(
					"Nested objects don't match.\n%s\n%s\n"
					, print_r($reference,1)
					, print_r($sample,1)
				)
			);

			$prev = $reference;
		}
	}
}