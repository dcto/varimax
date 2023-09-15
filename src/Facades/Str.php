<?php
/**
 * Str class
 * 
 * @method static void macro(string $name, object|callable $macro) Register a custom macro.
 * @method static void mixin(object $mixin, bool $replace = true) Mix another object into the class.
 * @method static bool hasMacro(string $name) Checks if macro is registered.
 * @method static string ableof(string $string) Get a new stringable object from the given string.
 * @method static string after(string $subject, string $search) Return the remainder of a string after the first occurrence of a given value.
 * @method static string afterLast(string $subject, string $search) Return the remainder of a string after the last occurrence of a given value.
 * @method static string ascii(string $value, string $language = 'en') Transliterate a UTF-8 value to ASCII.
 * @method static string before(string $subject, string $search) Get the portion of a string before the first occurrence of a given value.
 * @method static string beforeLast(string $subject, string $search) Get the portion of a string before the last occurrence of a given value.
 * @method static string between(string $subject, string $from, string $to) Get the portion of a string between two given values.
 * @method static string camel(string $value) Convert a value to camel case.
 * @method static bool contains(string $haystack, string|string[] $needles) Determine if a given string contains a given substring.
 * @method static bool containsAll(string $haystack, array $needles) Determine if a given string contains all array values.
 * @method static bool endsWith(string $haystack, string|string[] $needles) Determine if a given string ends with a given substring.
 * @method static string finish(string $value, string $cap) Cap a string with a single instance of a given value.
 * @method static bool is(string|array $pattern, string $value) Determine if a given string matches a given pattern.
 * @method static bool isAscii(string $value) Determine if a given string is 7 bit ASCII.
 * @method static bool isUuid(string $value) Determine if a given string is a valid UUID.
 * @method static string kebab(string $value) Convert a string to kebab case.
 * @method static intlength(string $value, string|null $encoding = null) Return the length of the given string.
 * @method static string limit(string $value, int $limit = 100, string $end = '...') Limit the number of characters in a string.
 * @method static string lower(string $value) Convert the given string to lower-case.
 * @method static string words(string $value, int $words = 100, string $end = '...') Limit the number of words in a string.
 * @method static string padBoth(string $value, int $length, string $pad = ' ') Pad both sides of a string with another.
 * @method static string padLeft(string $value, int $length, string $pad = ' ') Pad the left side of a string with another.
 * @method static string padRight(string $value, int $length, string $pad = ' ') Pad the right side of a string with another.
 * @method static mixxed parseCallback(string $callback, string|null $default = null) Parse a Class[@]method style callback into class and method.
 * @method static string plural(string $value, int $count = 2) Get the plural form of an English word.
 * @method static string pluralStudly(string $value, int $count = 2) Pluralize the last word of an English, studly caps case string.
 * @method static string random(int $length = 16) Generate a more truly "random" alpha-numeric string.
 * @method static string replaceArray(string $search, array $replace, string $subject) Replace a given value in the string sequentially with an array.
 * @method static string replaceFirst(string $search, string $replace, string $subject) Replace the first occurrence of a given value in the string.
 * @method static string replaceLast(string $search, string $replace, string $subject) Replace the last occurrence of a given value in the string.
 * @method static string start(string $value, string $prefix) Begin a string with a single instance of a given value.
 * @method static string upper(string $value) Convert the given string to upper-case.
 * @method static string title(string $value) Convert the given string to title case.
 * @method static string singular(string $value) Get the singular form of an English word.
 * @method static string slug(string $title, string $separator = '-', string|null $language = 'en') Generate a URL friendly "slug" from a given string.
 * @method static string snake(string $value, string $delimiter = '_') Convert a string to snake case.
 * @method static bool startsWith(string $haystack, string|string[] $needles) Determine if a given string starts with a given substring.
 * @method static string studly(string $value) Convert a value to studly caps case.
 * @method static string substr(string $string, int $start, int|null $length = null) Returns the portion of string specified by the start and length parameters.
 * @method static intsubstrCount(string $haystack, string $needle, int $offset = 0, int|null $length = null) Returns the number of substring occurrences.
 * @method static string ucfirst(string $string) Make a string's first character uppercase.
 * @method static UuidInterface uuid() Generate a UUID (version 4) 
 * @method static UuidInterfaceordered Uuid() Generate a time-ordered UUID (version 4) 
 * @method static void createUuidsUsing(callable $factory = null) Set the callable that will be used to generate UUIDs.
 * @method static void createUuidsNormally() Indicate that UUIDs should be created normally and not using a custom factory.
 */
class Str extends \Illuminate\Support\Str
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'str';
    }
}
