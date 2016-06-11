<?php
#
# Written by Chris Cowan
# April 4th, 2013
# License unknown
#
function bidi_cleanup($str){
	# Closes all embedded RTL and LTR unicode formatting blocks in a string so that
	# it can be used inside another without controlling its direction.
	# More info: http://www.iamcal.com/understanding-bidirectional-text/
	#
	# LRE - U+202A - 0xE2 0x80 0xAA
	# RLE - U+202B - 0xE2 0x80 0xAB
	# LRO - U+202D - 0xE2 0x80 0xAD
	# RLO - U+202E - 0xE2 0x80 0xAE
	#
	# PDF - U+202C - 0xE2 0x80 0xAC
	#
	$explicits	= '\xE2\x80\xAA|\xE2\x80\xAB|\xE2\x80\xAD|\xE2\x80\xAE';
	$pdf		= '\xE2\x80\xAC';

	$stack = 0;
	$str = preg_replace_callback("!(?<explicits>$explicits)|(?<pdf>$pdf)!", function($match) use (&$stack) {
		if (isset($match['explicits']) && $match['explicits']) {
			$stack++;
		} else {
			if ($stack)
				$stack--;
			else
				return '';
		}
		return $match[0];
	}, $str);
	for ($i=0; $i<$stack; $i++){
		$str .= "\xE2\x80\xAC";
	}
	return $str;
}
