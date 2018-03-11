<?php

// a-z 0-9 : "a b c d e f g h i j k l m n o p q r s t u v w x y z 0 1 2 3 4 5 6 7 8 9"
function random_string($max, $alphabet = 'a b c d e f g h i j k l m n p q r s t u v w x y z 1 2 3 4 5 6 7 8 9')
{
    if ($alphabet == '') {
        $alphabet = 'a b c d e f g h i j k l m n p q r s t u v w x y z 1 2 3 4 5 6 7 8 9';
    }

    $chars = explode(' ', $alphabet);
    for ($i = 0; $i < $max; ++$i) {
        $rnd = array_rand($chars);
        $rtn .= ($chars[$rnd]);
    }
    $STRING = substr(str_shuffle(strtolower($rtn)), 0, $max);

    return $STRING;
}
