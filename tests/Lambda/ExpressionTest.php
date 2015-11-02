<?php
namespace Haldayne\Boost\Lambda;

class ExpressionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provides_expressions_and_io
     */
    public function test_execution($expression, array $input, $output)
    {
        $fn = new Expression($expression);
        $this->assertSame($output, call_user_func_array($fn, $input));
    }

    /**
     * @dataProvider provides_invalid_expressions
     * @expectedException \InvalidArgumentException
     */
    public function test_throws_invalidargument($expression)
    {
        new Expression($expression);
    }

    public function test_caching()
    {
        $e = '$_0 < $_1';
        $x = new Expression($e); // different objects w/ same expression...
        $y = new Expression($e); // ... should have same callable
        $this->assertSame($x->getCallable(), $y->getCallable());
    }

    // -=-= Data Provider =-=-

    public static function provides_expressions_and_io()
    {
        return [
            [ 'strlen', [ 'foo' ], 3 ],
            [ function ($x) { return $x+1; }, [ 241 ], 242 ],
            [ '$_0 == $_1', [ 1, 1 ], true ],
            [ '$_0 == $_1', [ 1, 0 ], false ],
            [ '$_0 == $_1', [ 0 ], true ],
            [ '$_0 == $_1', [ ], true ],
        ];
    }

    public static function provides_invalid_expressions()
    {
        return [
            [ false ],
            [ 242 ],
            [ 1.1 ],
            [ [] ],
            [ new \StdClass ],
        ];
    }
}
