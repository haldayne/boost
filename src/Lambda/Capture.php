<?php
namespace Haldayne\Boost\Lambda;

use Haldayne\Boost\Map;

/**
 * Runs a callable, capturing all error handlers, then provides a method to
 * extract any errors thrown during execution.
 *
 * ```
 * $lambda = new Capture(function ($src, $dst) { return copy($src, $dst); });
 * if (false === $lambda('foo', 'bar')) {
 *     $lambda->getCapturedErrors
 * }
 * ```
 * 
 */
class Capture
{
    public function __construct(callable $code)
    {
        $this->code = $code;
        $this->map = new Map();
    }

    public function __invoke()
    {
        $old_handler = set_error_handler(
            function ($code, $message, $file, $line, $context) {
                $this->map->push(compact('code', 'message', 'file', 'line', 'context'));
            }
        );
        call_user_func_array($this->code, func_get_args());
        set_error_handler($old_handler);
    }

    public function getCapturedErrors()
    {
        return $this->map;
    }

    // PRIVATE API

    private $code = null;
    private $map = null;
}
