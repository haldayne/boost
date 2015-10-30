<?php
namespace Haldayne\Boost\Lambda;

/**
 * Runs a callable, retrying using exponential back-off if the callable fails.
 *
 * ```
 * use Haldayne\Boost\Lambda\Backoff;
 * $lambda = new Retry(function ($src, $dst) { return copy($src, $dst); });
 * $lambda->setAttempts(3);
 * if (false === $lambda('foo', 'bar')) {
 *     throw new \RuntimeException(sprintf(
 *          'Failed to copy file after %d tries',
 *          $lambda->getAttempts()
 *     ));
 * }
 * ```
 * Network resources may not respond immediately, owing to *temporary* issues
 * in resource availability. The usual technique for dealing with these error
 * responses is to implement retry logic in the client. This technique
 * increases reliability of the client and reduces operational costs for the
 * developer.
 *
 * If the given callback returns exactly boolean false, Retry will attempt
 * the callback again after waiting exponentially longer times: 1 second,
 * 2 seconds, 4 seconds, 8 seconds, etc. up to the maximum attempts limit.
 * The default attempt limit is 5.  Also, you can change the back-off
 * strategy if you'd like.
 */
class Retry
{
    public function __construct(callable $code)
    {
        $this->code     = $code;
        $this->attempts = 5;
        $this->strategy = function ($n) { sleep(pow(2, $n-1)); };
    }

    public function __invoke()
    {
        $n = 0;
loop:
        $result = call_user_func_array($this->code, func_get_args());
        if (false === $result) {
            if (++$n < $this->attempts) {
                call_user_func($this->strategy, $n-1);
                goto loop;
            }
        }
        return $result;
    }

    public function setAttempts($attempts)
    {
        if (is_numeric($attempts) && 0 <= intval($attempts)) {
            $this->attempts = intval($attempts);
            return $this;
        } else {
            throw new \InvalidArgumentException('Argument $attempts must be whole number');
        }
    }

    public function getAttempts()
    {
        return $this->attempts;
    }

    public function setStrategy(callable $callable)
    {
        $this->strategy = $callable;
        return $this;
    }

    public function getStrategy()
    {
        return $this->strategy;
    }

    // PRIVATE API

    private $code = null;
    private $attempts = null;
    private $strategy = null;
}
