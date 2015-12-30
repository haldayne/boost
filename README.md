# *Make the common stuff easy (and legible).*

Most PHP programming consists of data-pushing between a database and a browser, which means a lot of string & array processing.  Think about splitting strings, walking arrays, and so on.  Sadly, when it comes to this common stuff, PHP is horribly verbose compared to JS, python, etc.  This library addresses this sadness with:

* toolbox approach to string manipulation (*TODO*)
* callable-driven, fluent interfaces for array processing

# Let's get started

You need at least PHP 5.5.0.  No other extensions are required.

Install via composer: `php composer.phar require haldayne/boost ^1.0`

# Using Boost

[Read the docs](http://haldayne-docs.readthedocs.org/en/latest/boost) for complete examples. For an overview, read on.

## Readable, chainable array processing with `Map`

PHP arrays are a collection of `<key, value>` pairs.  The computer science term for this is an [associative array](https://en.wikipedia.org/wiki/Associative_array), also called a dictionary, a hash, or a map.  Boost needs a name for its improvement on PHP arrays, and `Map` is good because it's *easy to type* in those insane use statements:

    use Haldayne\Boost\Map;

Now we can use Map's fluent interface to solve real problems:

* use anything as a key: scalars, even *arrays and objects*
* one liner solutions for...
   * map/reduce
   * filter/each
   * partition (aka group by)
   * apply same method to a bunch of objects
   * ... and more
* throw-away callables until PHP supports short closures
   * `$numbers->grep('$v % 2')` vs.
     `$numbers->grep(function ($v) { return $v % 2; })`
* enforce "Array of Type" type hinting:
   * `Haldayne\Boost\MapOfInts`
   * `Haldayne\Boost\MapOfFloats`
   * `Haldayne\Boost\MapOfStrings`
   * `Haldayne\Boost\MapOfCollections`
   * `Haldayne\Boost\MapOfNumerics`
   * `Haldayne\Boost\MapOfObjects`
   * ... or create your own!

# Related projects

:alien: :heavy_minus_sign: [Chain, a consistent and chainable way to work with arrays in PHP.](https://github.com/cocur/chain)

> Working with arrays in PHP is a mess. First of all, you have to prefix most (but not all) functions with array_, the parameter ordering is not consistent. For example, array_map() expects the callback as first parameter and the array as the second, but array_filter() expects the array as first and the callback as second. You also have to wrap the function calls around the array, the resulting code is not nice to look at, not readable and hard to understand.

:alien: :heavy_minus_sign: [Ardent, a Collections library for PHP.](https://github.com/morrisonlevi/Ardent)

> While developing and helping others develop PHP applications I noticed the trend to use PHP's arrays in nearly every task. Arrays in PHP are useful but are overused because PHP doesn't have rich standard libraries for working with common data structures and algorithms. This library hopes to fill in that gap. Undoubtably, I've made mistakes in design and implementation; hopefully more community involvement can help identify and fix such mistakes.


:alien: :heavy_minus_sign: [PHP Collection - Basic collections for PHP.](http://jmsyst.com/libs/PHP-Collection)

> Collections can be seen as more specialized arrays for which certain contracts are guaranteed.
>
> Supported Collections:
> * Sequences
> * Maps
> * Sets (not yet implemented)
