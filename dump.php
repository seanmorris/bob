<?php

require __DIR__ . '/vendor/autoload.php';

while($reference = substr(fgets(STDIN),1))
{
	var_dump(\SeanMorris\Bob\Bank::decode($reference));
}

print PHP_EOL;
