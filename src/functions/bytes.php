<?php
namespace Haldayne\Boost;

/**
 * `int bytes(string)` - Return the number of bytes in a string.
 *
 * PHP documents that the `strlen` function returns the number of bytes
 * in a string. This is true, unless the `mbstring` extension has
 * overridden `strlen` to return the number of characters.
 *
 * You can either rely entirely on the `bytes` function in your code, or you
 * can mask the built-in strlen with function use (PHP 5.6+):
 *
 * ```
 * // make strlen actually count bytes, regardless of whether mbstring
 * // extension is overloading
 * use Haldayne\Boost\bytes as strlen;
 * ```
 *
 * @see http://php.net/manual/en/function.strlen.php Documents `strlen`
 * @see http://php.net/manual/en/mbstring.overload.php Discusses function overload
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
