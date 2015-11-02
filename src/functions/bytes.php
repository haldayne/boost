<?php
namespace Haldayne\Boost;

/**
 * `int bytes(string)` - Return the number of bytes in a string.
 *
 * PHP documents that the `strlen` function returns the number of bytes
 * in a string. This is true, unless the `mbstring` extension has
 * overridden `strlen` to return the number of characters.
 * 
 * This function always returns the number of bytes in a string, regardless
 * of any mbstring overrides.
 *
 * @see http://php.net/manual/en/function.strlen.php
 * @see http://php.net/manual/en/mbstring.overload.php
 */
if (2 & ini_get('mbstring.func_overload')) {
    function bytes($string) {
        return mb_strlen($string, '8bit');
    }
} else {
    function bytes($string) {
        return strlen($string);
    }
}
