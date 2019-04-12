<?php

// 应用公共文件

/**
 * 数组转xml
 * @param unknown $arr
 * @return string
 */
if (!function_exists('arrtoxml')) {
    function arrtoxml($arr) {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
}

/**
 * xml转数组
 * @param xml $xml
 * @return mixed
 */
if (!function_exists('xmltoarr')) {
    function xmltoarr($xml) {
        $re = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        return json_decode(json_encode($re), true);
    }
}
