<?php

require __DIR__ . '/vendor/autoload.php';

function dumpBytes($blob)
{
	for($i = 0; $i < strlen($blob); $i++)
	{
		$bytes[] = str_pad(dechex(ord($blob[$i])), 2, '0', STR_PAD_LEFT);
	}

	print implode(' ', $bytes) . PHP_EOL;
}

$tests = [
	'SeanMorris\Bob\Test\TextTest'
	, 'SeanMorris\Bob\Test\NumberTest'
	, 'SeanMorris\Bob\Test\ListTest'
	, 'SeanMorris\Bob\Test\AssocTest'
	, 'SeanMorris\Bob\Test\ObjectTest'
];

array_map(
	function($testClass) {
		$test = new $testClass;
		$test->run(new \TextReporter());
		echo PHP_EOL;
	}
	, $tests
);