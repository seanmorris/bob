<?php

require __DIR__ . '/vendor/autoload.php';

while($reference = fgets(STDIN))
{
	fputcsv(STDOUT, (array) \SeanMorris\Bob\Bank::decode($reference));
}
