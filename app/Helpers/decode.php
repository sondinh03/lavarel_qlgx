<?php

if (! function_exists('gm')) {
    function gm($string): string
    {
        $string = preg_replace('/A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z/', '', $string);
        $result = '';
        for ($t = 0; $t < strlen($string) - 1; $t += 2) {
            $result .= mb_chr(intval(substr($string, $t, 2), 16));
        }

        return $result;
    }
}
