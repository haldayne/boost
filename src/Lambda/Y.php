<?php
namespace Haldayne\Boost\Lambda;

/**
 * Implements the Y-combinator.
 *
 * It's possible to express a "recursive" function -- like Fibonacci and
 * factorials -- without recursion or iteration. Using a surprising and
 * admittedly dark magic approach discovered by Haskell Curry called the Y-
 * combinator, you can use lambda functions to calculate fixed-points and
 * to solve around them:
 *
 * ```
 * // calculates n-th Fibonacci using two fixed points
 * // this version runs in exponential time
 * $fibonacci = new Y(function ($fibonacci) { 
 *     return function ($n) use ($fibonacci) {
 *         switch ($n) {
 *         case 0:  return 0;
 *         case 1:  return 1;
 *         default: return $fibonacci($n-1) + $fibonacci($n-2);
 *         }
 *     }
 * });
 * var_dump($fibonacci(6));
 * ```
 *
 * @TODO
 * The recursive callable which we pass to the Y-combinator has exponential
 * run-time. But, we can change that to linear time just by adding caching
 * to the internal combinator. That's where this class comes in: it internally
 * caches intermediary results, so that simple recursive definitions have
 * optimal performance without having to implement their own caching.
 * 
 * @see https://php100.wordpress.com/2009/04/13/php-y-combinator/
 * @see http://matt.might.net/articles/implementation-of-recursive-fixed-point-y-combinator-in-javascript-for-memoization/
 */
class Y
{
    public function __construct(callable $code)
    {
        $this->code = $code;
    }

    public function __invoke()
    {
        return call_user_func_array(
            static::combinator(),
            func_get_args()
        );
    }

    // PRIVATE API

    private $code = null;
    private $cache = [];

    private function combinator() {
        $func = function ($f) { return $f($f); };
        return $func(function ($f) {
            $code = $this->code;
            return $code(function () use ($f) {
                $ff = $f($f);
                return call_user_func_array($ff, func_get_args());
            });
        });
    }
}
