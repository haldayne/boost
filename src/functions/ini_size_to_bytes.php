<?php
namespace Haldayne\Boost;

/**
 * `int ini_size_to_bytes(string)` - Convert a short-hand bytes specification
 * into integer bytes.
 *
 * Some PHP ini values (like `upload_max_filesize`) allow you to specify the
 * number of bytes in a short-hand, like "10M" or "1G". But, when you call
 * `ini_get` you'll get these actual short-hand values, instead of the byte
 * equivalent (which usually you want).
 *
 * This function converts a super-set of documented short-hand into bytes:
 *   * An optional leading +
 *   * Number specification in hex, octal, or decimal
 *   * Kilo-, Mega-, Giga-, and Tera-bytes expressed using single or double
 *     characters in either lower or upper case: "k", "kB", "G", "Pb", etc.
 *
 * @see http://php.net/manual/en/faq.using.php#faq.using.shorthandbytes Documents the short-hand
 */
function ini_size_to_bytes($string)
{
    // eliminate complexity: go all lower
    $string = strtolower($string);

    // we don't care about any leading '+' or any trailing 'b', drop them
    $string = ltrim($string, '+');
    $string = rtrim($string, 'b');

    // convert base
    if (0 === strpos($string, '0x')) {
        $number = intval($string, 16);
    } else if (0 === strpos($string, '0')) {
        $number = intval($string, 8);
    } else {
        $number = intval($string);
    }

    // multiply based on last character
    switch (substr($string, -1)) {
        case 't': $number *= 1099511627776; break;
        case 'g': $number *= 1073741824;    break;
        case 'm': $number *= 1048576;       break;
        case 'k': $number *= 1024;          break;
    }

    return $number;
}
