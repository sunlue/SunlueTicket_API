<?php
/**
 * User: xiebing
 * Date: 2018/10/8
 * Time: 16:06
 */

namespace ticket\api\model;

use ticket\common\model\Common;

class PayConfig extends Common {
    protected $pk = 'id';

    protected function setConfigAttr($value) {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }

    protected function getConfigAttr($value) {
        return json_decode($value, true);
    }

    public function get($where = array(), $cache = false) {
        $data = PayConfig::alias('a')->field('id,is_del,last_modify_time', true)->cache($cache)->where($where)->find();
        return $data ? $data->toArray() : array();
    }

}