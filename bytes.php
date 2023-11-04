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

while($reference = fgets(STDIN))
{
	dumpBytes($reference);
}
