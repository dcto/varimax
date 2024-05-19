<?php

namespace VM\Http\Response;

use SimpleXMLElement;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class Encode
{

    /**
     * @param mixed $data
     * @return string
     */
    public static function toRaw($data)
    {
        return is_scalar($data) ? (string) $data : print_r($data, true);
    }


    /**
     * @param array|Arrayable $data
     * @param SimpleXMLElement|null $parentNode
     * @param string $root
     * @return string
     */
    public static function toXml($data, $parentNode = null, $root = 'root')
    {
        $data = (array) $data;
        if ($parentNode === null) {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>' . "<{$root}></{$root}>");
        } else {
            $xml = $parentNode;
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                self::toXml($value, $xml->addChild($key));
            } else {
                if (is_numeric($key)) {
                    $xml->addChild('item' . $key, (string) $value);
                } else {
                    $xml->addChild($key, (string) $value);
                }
            }
        }
        return trim($xml->asXML());
    }


    /**
     * @param string $xml
     * @return array
     */
    public static function toArray($xml)
    {
        // For PHP 8.0, libxml_disable_entity_loader() has been deprecated.
        // As libxml 2.9.0 is now required, external entity loading is guaranteed to be disabled by default.
        // And this function is no longer needed to protect against XXE attacks, unless the (still vulnerable). LIBXML_NOENT is used.
        // In that case, it is recommended to refactor the code using libxml_set_external_entity_loader() to suppress loading of external entities.
        if (\PHP_VERSION_ID < 80000) {
            $disableLibxmlEntityLoader = libxml_disable_entity_loader(true);
            $respObject = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOERROR);
            libxml_disable_entity_loader($disableLibxmlEntityLoader);
        } else {
            $respObject = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOERROR);
        }

        if ($respObject === false) {
            throw new \InvalidArgumentException('Syntax error.');
        }

        return json_decode(json_encode($respObject), true);
    }

    /**
     * @param array|Arrayable|Jsonable $data
     * @return string
     */
    public static function toJson($data, $callback = null, int $flags = JSON_UNESCAPED_UNICODE, int $depth = 512)
    {
        if ($data instanceof Jsonable) {
            return (string) $data;

        }else if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        $data = json_encode($data, $flags | JSON_THROW_ON_ERROR, $depth);

        if ($callback) {
            // partially taken from https://geekality.net/2011/08/03/valid-javascript-identifier/
            // partially taken from https://github.com/willdurand/JsonpCallbackValidator
            //      JsonpCallbackValidator is released under the MIT License. See https://github.com/willdurand/JsonpCallbackValidator/blob/v1.1.0/LICENSE for details.
            //      (c) William Durand <william.durand1@gmail.com>
            $pattern = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*(?:\[(?:"(?:\\\.|[^"\\\])*"|\'(?:\\\.|[^\'\\\])*\'|\d+)\])*?$/u';
            $reserved = [
                'break', 'do', 'instanceof', 'typeof', 'case', 'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue', 'for', 'switch', 'while',
                'debugger', 'function', 'this', 'with', 'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum', 'extends', 'super',  'const', 'export',
                'import', 'implements', 'let', 'private', 'public', 'yield', 'interface', 'package', 'protected', 'static', 'null', 'true', 'false',
            ];
            $parts = explode('.', $callback);
            foreach ($parts as $part) {
                if (!preg_match($pattern, $part) || \in_array($part, $reserved, true)) {
                    throw new \InvalidArgumentException('The callback name is not valid.');
                }
            }
            
            $data = sprintf('/**/%s(%s);', $callback, $data);
        }

        return $data;

    }
}