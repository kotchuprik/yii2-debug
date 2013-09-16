<?php
/**
 * @author Constantin Chuprik <constantinchuprik@gmail.com>
 *
 * @package Yii2Debug
 */
class Yii2DebugVarExporter
{
    protected static $_objects;
    protected static $_output;
    protected static $_depth;

    public static function export($var, $depth = 10)
    {
        self::$_output = '';
        self::$_objects = array();
        self::$_depth = $depth;
        self::_exportInternal($var, 0);

        return self::$_output;
    }

    protected static function _exportInternal($var, $level)
    {
        switch (gettype($var)) {
            case 'boolean':
                self::$_output .= $var ? 'true' : 'false';
                break;
            case 'integer':
                self::$_output .= $var;
                break;
            case 'double':
                self::$_output .= '\'' . $var . '\'';
                break;
            case 'string':
                self::$_output .= '\'' . addslashes($var) . '\'';
                break;
            case 'resource':
                self::$_output .= '{resource}';
                break;
            case 'NULL':
                self::$_output .= 'null';
                break;
            case 'unknown type':
                self::$_output .= '{unknown}';
                break;
            case 'array':
                if (self::$_depth <= $level) {
                    self::$_output .= 'array(...)';
                } elseif (empty($var)) {
                    self::$_output .= 'array()';
                } else {
                    $keys = array_keys($var);
                    $spaces = str_repeat(' ', $level * 4);
                    self::$_output .= 'array(';
                    foreach ($keys as $key) {
                        self::$_output .= PHP_EOL . $spaces . '    ';
                        self::_exportInternal($key, 0);
                        self::$_output .= ' => ';
                        self::_exportInternal($var[$key], $level + 1);
                        self::$_output .= ',';
                    }
                    self::$_output .= PHP_EOL . $spaces . ')';
                }
                break;
            case 'object':
                if (($id = array_search($var, self::$_objects, true)) !== false) {
                    self::$_output .= get_class($var) . '#' . ($id + 1) . '(...)';
                } elseif (self::$_depth <= $level) {
                    self::$_output .= get_class($var) . '(...)';
                } else {
                    $id = array_push(self::$_objects, $var);
                    $className = get_class($var);
                    $members = (array)$var;
                    $spaces = str_repeat(' ', $level * 4);
                    self::$_output .= $className . '#' . $id . PHP_EOL . $spaces . '(';
                    foreach ($members as $key => $value) {
                        $keyDisplay = strtr(trim($key), array("\0" => ':'));
                        self::$_output .= PHP_EOL . $spaces . '    [' . $keyDisplay . '] => ';
                        self::_exportInternal($value, $level + 1);
                    }
                    self::$_output .= PHP_EOL . $spaces . ')';
                }
                break;
        }
    }
}
