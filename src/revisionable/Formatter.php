<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 16-10-31 上午 10:52
 */

namespace Runner\Revisionable;

class Formatter
{

    protected static $extFormatters = [];


    public static function extend($name, $callback)
    {
        self::$extFormatters[$name] = $callback;
    }


    public static function format($rule, $value)
    {
        $rule = explode(':', $rule, 2);

        $format = $rule[0];
        $parameters = isset($rule[1]) ? explode(',', $rule[1]) : [];

        try {
            return forward_static_call_array(
                [Formatter::class, 'format' . ucfirst($format)],
                array_merge([$value], $parameters)
            );
        }catch (\Exception $e) {
            try {
                return call_user_func_array(
                    self::$extFormatters[$format],
                    array_merge([$value], $parameters)
                );
            }catch (\Exception $e) {}
        }

        return $value;
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


    public static function formatTranslate($value, $string)
    {
        foreach (explode('|', $string) as $item) {
            list($k, $v) = explode('=', $item, 2);
            if($k == $value) {
                return $v;
            }
        }
        return $value;
    }
}