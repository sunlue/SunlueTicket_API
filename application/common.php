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

/**
 * 得到日期范围
 * @param $start_date
 * @param $end_date
 * @return array
 */
if (!function_exists('get_date_from_range')) {
    function get_date_from_range($start_date, $end_date, $format = '') {
        $s_timestamp = strtotime($start_date);
        $e_timestamp = strtotime($end_date);
        // 计算日期段内有多少天
        $days = ($e_timestamp - $s_timestamp) / 86400 + 1;
        // 保存每天日期
        $date = array();
        for ($i = 0; $i < $days; $i++) {
            $date[] = date('Y' . $format . 'm' . $format . 'd', $s_timestamp + (86400 * $i));
        }
        return $date;
    }
}
