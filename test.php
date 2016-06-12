<?php
	#
	# the setup
	#

	include('2009-03-01.php');
	include('2013-04-04.php');
	include('2016-06-11.php');

	$map = array(
		'2009-03-01' => 'unicode_cleanup_rtl',
		'2013-04-04' => 'bidi_cleanup',
		'2016-06-11' => 'normalizeBidirectionalTextMarkers',
	);

	$lre = "\xE2\x80\xAA";
	$rle = "\xE2\x80\xAB";
	$lro = "\xE2\x80\xAD";
	$rlo = "\xE2\x80\xAE";
	$pdf = "\xE2\x80\xAC";

	$tests = array(
		array("Simple", "hello", "hello"),
		array("Balanaced", "foo{$lre}bar{$pdf}baz", "foo{$lre}bar{$pdf}baz"),
		array("Pre-pop", "{$pdf}foo{$rle}bar{$pdf}baz", "foo{$rle}bar{$pdf}baz"),
		array("Pre-2pop", "{$pdf}foo{$pdf}{$rle}bar{$pdf}baz", "foo{$rle}bar{$pdf}baz"),
		array("Post-pop", "foo{$rlo}bar{$pdf}baz{$pdf}", "foo{$rlo}bar{$pdf}baz"),
		array("Post-2pop", "foo{$rlo}bar{$pdf}{$pdf}baz{$pdf}", "foo{$rlo}bar{$pdf}baz"),
		array("Missing-pop", "foo{$lro}bar", "foo{$lro}bar{$pdf}"),
		array("Missing-2pop", "foo{$lro}bar{$rlo}baz", "foo{$lro}bar{$rlo}baz{$pdf}{$pdf}"),
	);


	#
	# run tests
	#

	$results = array();
	foreach ($tests as $k => $v){
		foreach ($map as $k2 => $v2){
			$out = call_user_func($v2, $v[1]);
			$results["{$k2}||$k"] = $out;
		}
	}


	#
	# show table
	#

	$keys = array();
	foreach ($tests as $row) $keys[] = strlen($row[0]);
	$lbl_len = max($keys);

	echo str_repeat(' ', $lbl_len)." | ".implode(' | ', array_keys($map))."\n";
	echo str_repeat('-', $lbl_len).str_repeat('-|-----------', count($map))."-\n";

	foreach ($tests as $k => $v){
		echo str_pad($v[0], $lbl_len);
		foreach ($map as $k2 => $v2){
			$expected = $v[2];
			$got = $results["$k2||$k"];
			$pass = $expected === $got;
			echo $pass ? ' |       pass' : ' |       FAIL';
		}
		echo "\n";
	}


	#
	# output any problems
	#

	if (false){
	foreach ($tests as $k => $row){
		echo "\n";
		echo "TEST: {$row[0]}\n";
		echo "EXPECT     : ".urlencode($row[2])."\n";

		foreach ($map as $k2 => $v2){
			echo "$k2 : ".urlencode($results["$k2||$k"])."\n";
		}
	}
	}


	#
	# performance
	#

	echo "\n";
	echo "Performance:\n";

	$test_text = str_repeat("Hello {$lre}world{$pdf}. ", 1000)."{$pdf}";
	$bytes = strlen($test_text);
	$loops = 10;

	foreach ($map as $k => $v){
		$t0 = microtime(true);
		for ($i=0; $i<$loops; $i++){
			$out = call_user_func($v, $test_text);
		}
		$t1 = microtime(true);

		$rate = number_format(round(($bytes * $loops) / ($t1 - $t0)));
		echo "$k - $rate bytes per second\n";

	}
