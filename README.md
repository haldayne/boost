# *Make the common stuff easy (and legible).*

Most PHP programming consists of data-pushing between a database and a browser, which means a lot of string & array processing.  Think about splitting strings, walking arrays, and so on.  Sadly, when it comes this common stuff, PHP is horribly verbose compared to JS, python, etc.  This library addresses this sadness with:

* toolbox approach to string manipulation
* OO-fluent interfaces centered around callables for array processing

# Let's get started

You need at least PHP 5.5.0.  No other extensions are required.

Install via composer: `php composer.phar require haldayne/boost *`

# Using Boost

## Array processing with `Map`

We all know that PHP arrays are a collection of `<key, value>` pairs.  The computer science term for this is an [associative array](https://en.wikipedia.org/wiki/Associative_array), also called a dictionary, a hash, or a map.  Boost needs a name for its improvement on PHP arrays, and `Map` is good because it's *easy to type* in those insane use statements:

    use Haldayne\Boost\Map;

Now we can use Map's fluent interface to solve real problems:

* use anything as a key: scalars, even *arrays and objects*!
* one liner solutions for...
   * map/reduce
   * filter/each
   * partition (aka group by)
   * ... and more
* enforce "Array of Type" type hinting:
   * `Haldayne\Boost\MapOfBoolean`
   * `Haldayne\Boost\MapOfInteger`
   * `Haldayne\Boost\MapOfFloat`
   * `Haldayne\Boost\MapOfString`
   * `Haldayne\Boost\MapOfScalar`
   * `Haldayne\Boost\MapOfArray`
   * `Haldayne\Boost\MapOfTraversable`
   * `Haldayne\Boost\MapOfObject`
   * `Haldayne\Boost\MapOfMap`
   * ... or create your own!

# Related projects

:alien: [Ardent, a Collections library for PHP.](https://github.com/morrisonlevi/Ardent)

> While developing and helping others develop PHP applications I noticed the trend to use PHP's arrays in nearly every task. Arrays in PHP are useful but are overused because PHP doesn't have rich standard libraries for working with common data structures and algorithms. This library hopes to fill in that gap. Undoubtably, I've made mistakes in design and implementation; hopefully more community involvement can help identify and fix such mistakes.


:alien: [PHP Collection - Basic collections for PHP.](http://jmsyst.com/libs/PHP-Collection)

> Collections can be seen as more specialized arrays for which certain contracts are guaranteed.
>
> Supported Collections:
> * Sequences
> * Maps
> * Sets (not yet implemented)
