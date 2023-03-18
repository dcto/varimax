<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Validator
 * @method static VM\Form\Validator is(bool $is)
 * @method static VM\Form\Validator setError(string $message)
 * @method static VM\Form\Validator error(string $message)
 * @method static VM\Form\Validator make(string $string)
 * @method static VM\Form\Validator set(string $string)
 * @method static VM\Form\Validator null(string $message = 'This field is required.')
 * @method static VM\Form\Validator eq(int $value, string $message = 'The both string %s and %s must be equal')
 * @method static VM\Form\Validator gt(int $value, string $message = 'The [%s] must be greater than %s .')
 * @method static VM\Form\Validator ge(int $value, string $message = 'The [%s] must be greater than and equal to %s .')
 * @method static VM\Form\Validator lt(int $value, string $message = 'The [%s] must be less than %s .')
 * @method static VM\Form\Validator le(int $value, string $message = 'The [%s] must be less than and equal to %s .')
 * @method static VM\Form\Validator length(int $limit, string $message='The [%s] length must be equal to %d character.')
 * @method static VM\Form\Validator min(int $limit, string $message='Input %s must be greater than %d length.')
 * @method static VM\Form\Validator max(int $limit, string $message='Input %s must be less than %d length.')
 * @method static VM\Form\Validator datetime(string $format, $message = 'The input datetime %s does not match of format %s.')
 * @method static VM\Form\Validator integer(string $message = 'The input %s is not a integer.')
 * @method static VM\Form\Validator float(string $message = 'The input %s is not a float.')
 * @method static VM\Form\Validator boolean(string $message = 'The input %s is not a boolean.')
 * @method static VM\Form\Validator url(string $message = 'The input url %s is not available.')
 * @method static VM\Form\Validator email(string $message = 'The email %s format was incorrect.')
 * @method static VM\Form\Validator ip(string $message = 'The input ip %s format was incorrect.')
 * @method static VM\Form\Validator mac(string $message = 'The input mac address %s format was incorrect.')
 * @method static VM\Form\Validator regExp(string $regexp, string $message = 'The input %s does not match the rule %s ')
 * @method static VM\Form\Validator username(string $message = null)
 * @method static VM\Form\Validator password(string $message = null)
 * @method static VM\Form\Validator chinese(string $message = null)
 * @method static VM\Form\Validator english(string $message = null)
 * @method static VM\Form\Validator price(string $message = null)
 * @method static VM\Form\Validator qq(string $message = null)
 * @method static VM\Form\Validator tel(string $message = null)
 * @method static VM\Form\Validator mobile(string $message = null)
 * @method static VM\Form\Validator zip(string $message = null)
 * @method static VM\Form\Validator phone(string $message = null)
 * @method static VM\Form\Validator idCard(string $message = null)
 * @method static VM\Form\Validator letter(string $message = null)
 * @method static VM\Form\Validator sex(string $message = null)
 * @method static VM\Form\Validator credit(string $message = null)
 *
 */
class Validator extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'validator';
    }
}
