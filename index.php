<?php
	require("N11library.php");

	$options = [
		'appKey' => '{appKey}',
        'appSecret' => '{appSecret}'
	];
	
	$n11 = new N11library();

	$n11->__setOptions($options);