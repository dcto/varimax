<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Arr
 *
 * @method static void macro(string $name, object|callable $macro) Register a custom macro.
 * @method static void mixin(object $mixin, bool  $replace = true) Mix another object into the class.
 * @method static bool hasMacro(string $name) Checks if macro is registered.
 * @method static bool accessible(mixed $value) Determine whether the given value is array accessible.
 * @method static array add(array $array, string $key, mixed $value) Add an element to an array using "dot" notation if it doesn't exist.
 * @method static array collapse(iterable $array) Collapse an array of arrays into a single array.
 * @method static array crossJoin(iterable ...$arrays) Cross join the given arrays, returning all possible permutations.
 * @method static array divide(array $array) Divide an array into two arrays. One with keys and the other with values.
 * @method static array dot(iterable $array, string $prepend = '') Flatten a multi-dimensional associative array with dots.
 * @method static array except(array $array, array|string $keys) Get all of the given array except for a specified array of keys.
 * @method static bool exists(ArrayAccess|array $array, string|int $key) Determine if the given key exists in the provided array.
 * @method static mixed first(iterable $array, callable $callback = null, mixed $default = null) Return the first element in an array passing a given truth test.
 * @method static mixed last(array $array, callable $callback = null, mixed $default = null) Return the last element in an array passing a given truth test.
 * @method static array flatten(iterable $array, int $depth = INF) Flatten a multi-dimensional array into a single level.
 * @method static void forget(array $array, array|string $keys) Remove one or many array items from a given array using "dot" notation.
 * @method static mixed get(ArrayAccess|array $array, string|int|null $key, mixed $default = null) Get an item from an array using "dot" notation.
 * @method static bool has(ArrayAccess|array $array, string|array $keys) Check if an item or items exist in an array using "dot" notation.
 * @method static bool hasAny(ArrayAccess|array $array, string|array $keys) Determine if any of the keys exist in an array using "dot" notation.
 * @method static bool isAssoc(array $array) Determines if an array is associative.
 * @method static array only(array $array, array|string $keys) Get a subset of the items from the given array.
 * @method static array pluck(iterable $array, string|array $value, string|array|null $key = null) Pluck an array of values from an array.
 * @method static array explodePluckParameters(string|array $value, string|array|null $key) Explode the "value" and "key" arguments passed to "pluck".
 * @method static array prepend(array $array, mixed $value, mixed $key = null) Push an item onto the beginning of an array.
 * @method static mixed pull(array $array, string $key, mixed $default = null) Get a value from the array, and remove it.
 * @method static mixed random(array $array, int|null $number = null) Get one or a specified number of random values from an array.
 * @method static array set(array $array, string|null $key, mixed $value) Set an array item to a given value using "dot" notation.
 * @method static array shuffle(array $array, int|null $seed = null) Shuffle the given array and return the result.
 * @method static array sort(array $array, callable|string|null $callback = null) Sort the array using the given callback or "dot" notation.
 * @method static array sortRecursive(array $array) Recursively sort an array by keys and values.
 * @method static stringquery(array $array) Convert the array into a query string.
 * @method static array where(array $array, callable $callback) Filter the array using the given callback.
 * @method static array wrap(mixed $value) If the given value is not an array and not null, wrap it in one.
 */
class Arr extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'arr';
    }
}
