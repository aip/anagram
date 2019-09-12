<?php

$checked = array();
function permute($str, $l, $r, $pspell_link) {

    global $checked;

    if ($l == $r) {

        if (empty($checked[$str])) {
            $checked[$str] = true;
            if (pspell_check($pspell_link, $str)) {
                echo "<li><strong>" . ucwords($str) . "</strong></li>\n";
            };
        }
    } else {
        for ($i = $l; $i <= $r; $i++) {
            $str = swap($str, $l, $i);
            permute($str, $l + 1, $r, $pspell_link);
            $str = swap($str, $l, $i);
        }
    }

}

function swap($a, $i, $j) {
    $charArray = str_split($a);
    $temp = $charArray[$i];
    $charArray[$i] = $charArray[$j];
    $charArray[$j] = $temp;
    return implode($charArray);
}

$anagram = "stop";
#$anagram = "abbreviation";

$str = "$anagram";
$n = strlen($str);

$pspell_link = pspell_new("en");


permute($str, 0, $n - 1, $pspell_link);
#p($checked);


?>
