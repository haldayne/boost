<?php
namespace Haldayne\Boost\Lambda;

use Haldayne\Boost\Map;

/**
 * Runs a callable, capturing all error messages, then provides a method to
 * extract any errors raised during execution.
 *
 * ```
 * use Haldayne\Boost\Lambda\Capture;
 * $lambda = new Capture(function ($src, $dst) { return copy($src, $dst); });
 * if (false === $lambda('foo', 'bar')) {
 *     throw new \RuntimeException($lambda->getCapturedErrors()->pop()->get('message'));
 * }
 * ```
 *
 * PHP itself as well as many libraries rely upon triggered errors to notify
 * developers of problems. Many frameworks provide methods to capure these
 * errors globally and convert them to exceptions, but you do not always have
 * access to those frameworks.  Worse, you may be using code that deliberately
 * silences errors.
 *
 * Every raised error inside the Capture lambda is pushed into a Map stack,
 * with each element also a Map of the error details: error code, error
 * string, file and line where raised, and an array context of variables
 * set at time of error.
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
                $this->map->push(
                    new Map(compact('code', 'message', 'file', 'line', 'context'))
                );
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
