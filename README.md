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
