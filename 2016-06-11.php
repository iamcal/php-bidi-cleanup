<?php
#
# Written by Lars Strojny
# June 11th, 2016
# https://gist.github.com/lstrojny/b4df50544515561586fb5c78277dfac6
#
function normalizeBidirectionalTextMarkers($graphemes)
{
    $regex = '/
         \x{202A} # LEFT-TO-RIGHT EMBEDDING
        |\x{202B} # RIGHT-TO-LEFT EMBEDDING
        |\x{202D} # LEFT-TO-RIGHT OVERRIDE
        |\x{202E} # RIGHT-TO-LEFT OVERRIDE
        |\x{202C} # POP DIRECTIONAL FORMATTING
    /ux';
    preg_match_all($regex, $graphemes, $markers, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
    if (count($markers) === 0) {
        return $graphemes;
    }
    #$popDirectionalFormatting = "\u{202C}";
    $popDirectionalFormatting = "\xE2\x80\xAC";
    $popDirectionFormattingLength = strlen($popDirectionalFormatting);
    $offset = $stack = 0;
    foreach ($markers as list(list($match, $markerPosition))) {
        // Increase for direction change markers, decrease for pop directional formatting markers
        $stack += $match === $popDirectionalFormatting ? -1 : 1;
        // More pops than embeddings/overrides, remove one
        if ($stack < 0) {
            // We need to use strlen() (and not mb_strlen()) here as preg_*() returns byte offsets
            $graphemes = substr($graphemes, 0, $markerPosition - $offset)
                       . substr($graphemes, $markerPosition + $popDirectionFormattingLength - $offset);
            $offset += $popDirectionFormattingLength;
            $stack = 0;
        }
    }
    // Close remaining markers
    $graphemes .= str_repeat($popDirectionalFormatting, $stack);
    return $graphemes;
}
