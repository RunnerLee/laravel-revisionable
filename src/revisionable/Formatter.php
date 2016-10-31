<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 16-10-31 上午 10:52
 */

namespace Runner\Revisionable;

class Formatter
{

    protected static $formatters = [
        'empty',
        'boolean',
        'string',
        'date',
    ];


    protected static $extFormatters = [];


    public static function extend($name, $callback)
    {
        self::$extFormatters[$name] = $callback;
    }


    public static function format($rule, $value)
    {
        list($format, $parameters) = explode(':', $rule, 2);

        if(in_array($format, self::$formatters)) {

            return forward_static_call_array(
                [Formatter::class, 'format' . ucfirst($format)],
                array_merge([$value], explode(',', $parameters))
            );
        }

        return call_user_func_array(
            self::$extFormatters[$format],
            array_merge([$value], explode(',', $parameters))
        );
    }


    public static function formatBoolean($value, $yes, $no)
    {
        return (!!$value ? $yes : $no);
    }


    public static function formatString($value, $format)
    {
        return sprintf($format, $value);
    }


    public static function formatDate($value, $format = 'Y-m-d H:i:s')
    {
        return date($format, strtotime($value));
    }
}