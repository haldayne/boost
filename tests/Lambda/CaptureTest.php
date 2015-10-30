<?php
namespace Haldayne\Boost\Lambda;

class CaptureTest extends \PHPUnit_Framework_TestCase
{
    public function test_captures_triggered_error()
    {
        $lambda = new Capture(function ($x) { trigger_error($x); });
        $lambda('foo');
        $errors = $lambda->getCapturedErrors();
        $this->assertSame(1, $errors->count());
        $this->assertSame(5, count($errors[0]));
        $this->assertSame('foo', $errors[0]['message']);
    }

    public function test_captures_silenced_error()
    {
        $lambda = new Capture(function ($x) { @trigger_error($x); });
        $lambda('foo');
        $errors = $lambda->getCapturedErrors();
        $this->assertSame(1, $errors->count());
        $this->assertSame(5, count($errors[0]));
        $this->assertSame('foo', $errors[0]['message']);
    }

    public function test_doesnt_clobber_existing_error_handler()
    {
        // setup custom error handler
        $n = 0;
        set_error_handler(function () use (&$n) { $n++; });

        // test custom error handler works before lambda call
        trigger_error('foo');
        $this->assertSame(1, $n);

        // try lambda call, ensure it doesn't call our custom handler
        $lambda = new Capture(function ($x) { @trigger_error($x); });
        $lambda('foo');
        $this->assertSame(1, $n);

        // test custom error handler works after lambda call
        trigger_error('foo');
        $this->assertSame(2, $n);
    }
}
