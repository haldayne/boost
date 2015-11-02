<?php
namespace Haldayne\Boost\Lambda;

/**
 * Manufactures a lambda from a string.
 *
 * In PHP, a callable can be a function name (as a string), a class or object
 * method specification (as an array [object|string, string], an anonymous
 * function (via create_function), or a closure (function () { ... }). Sadly,
 * the syntax for these callable methods may dwarf the meat of the code to
 * run:
 *
 * ```
 * uasort($array, function ($a, $b) { return $a <=> $b; });
 * // vs. an ideal expression
 * uasort($array, '$_0 <=> $_1');
 * ```
 *
 * This class wraps string-based code expressions in anonymous functions in a
 * reasonably performant manner. Libraries wanting to support concise
 * expressions as arguments to their functions can then use this class to
 * produce that effect:
 *
 * ```
 * use Haldayne\Boost\Lambda\Expression;
 * class Collection {
 *     public function __construct(array $items) {
 *         $this->items = $items;
 *     }
 *     // @param callable|string $expression
 *     public function filter($expression) {
 *         $fn = new Expression($expression);
 *         return array_filter($this->items, $fn);
 *     }
 * }
 *
 * $c = new Collection(['bee', 'bear', 'goose']);
 * var_dump(
 *    $c->filter('strlen($_0) < 4'),
 *    $c->filter(function ($x) { return strlen($x) < 4; })
 *    // same results, just different compactness
 * );
 * ```
 *
 * @see http://php.net/manual/en/language.types.callable.php
 * @see https://linepogl.wordpress.com/2011/07/09/on-the-syntax-of-closures-in-php/
 * @see http://justafewlines.com/2009/10/whats-wrong-with-php-closures/
 * @see https://wiki.php.net/rfc/short_closures
 * @see http://docs.hhvm.com/manual/en/hack.lambda.php
 * @see https://linepogl.wordpress.com/2011/08/04/short-closures-for-php-an-implementation/
 */
class Expression
{
    /**
     * Creates a callable returning the given expression.
     *
     * If the expression is already callable, returns it untouched. If the
     * expression is a string, a closure will be wrapped around the string
     * returning it as a single line. In this case, the first nine positional
     * arguments are available as $_0, $_1, ..., $_9.
     *
     * ```
     * $lt = new Expression('$_0 < $_1');
     * var_dump($lt(0, 1)); // true
     * var_dump($lt(1, 0)); // false
     * var_dump($lt());     // false (null not less than null)
     * var_dump($lt(-1));   // true (-1 is less than null)
     * ```
     * 
     * @param callable|string $expression
     * @throws \InvalidArgumentException When $expression not of expected type
     */
    public function __construct($expression)
    {
        if (is_callable($expression)) {
            $this->callable = $expression;
        
        } else if (is_string($expression)) {
            $this->callable = static::makeCallable($expression);

        } else {
            throw new \InvalidArgumentException(sprintf(
                'Argument $expression (of type %s) must callable or string',
                gettype($expression)
            ));
        }
    }

    public function __invoke()
    {
        return call_user_func_array($this->callable, func_get_args());
    }

    /**
     * Get the callable manufactured for this expression.
     *
     * @return callable
     */
    public function getCallable()
    {
        return $this->callable;
    }

    // PRIVATE API

    private static $map = [];
    private static function makeCallable($expression)
    {
        if (! array_key_exists($expression, static::$map)) {
            static::$map[$expression] = create_function(
                '$_0=null, $_1=null, $_2=null, $_3=null, $_4=null,' .
                '$_5=null, $_6=null, $_7=null, $_8=null, $_9=null ' ,
                'return (' . $expression . ');'
            );
        }
        return static::$map[$expression];
    }
}
