# SeanMorris\Bob

## Binary Object Bank

Store objects in binary. Maintain references and types.

Plans are in the works for a JavaScript version.

[![Build Status](https://travis-ci.org/seanmorris/bob.svg?branch=master)](https://travis-ci.org/seanmorris/bob) 

Usage:

```PHP
<?php

// Encode a value into a blob:

$blob = \SeanMorris\Bob\Bank::encode($someValue);

// Decode it back into whatever:

$someValue = \SeanMorris\Bob\Bank::decode($blob);
```

Run Tests:

```BASH
$ php runTests.php
SeanMorris\Bob\Test\TextTest
OK
Test cases run: 1/1, Passes: 45396, Failures: 0, Exceptions: 0
SeanMorris\Bob\Test\NumberTest
OK
Test cases run: 1/1, Passes: 27344, Failures: 0, Exceptions: 0
SeanMorris\Bob\Test\ListTest
OK
Test cases run: 1/1, Passes: 27488, Failures: 0, Exceptions: 0
SeanMorris\Bob\Test\AssocTest
OK
Test cases run: 1/1, Passes: 27488, Failures: 0, Exceptions: 0
SeanMorris\Bob\Test\ObjectTest
OK
Test cases run: 1/1, Passes: 512, Failures: 0, Exceptions: 0
```
