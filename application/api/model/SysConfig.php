<?php
/**
 * User: xiebing
 * Date: 2019-4-3
 * Time: 13:26
 */

namespace ticket\api\model;

use ticket\common\model\Common;

class SysConfig extends Common {
    protected $pk = 'id';

    protected function setConfigAttr($value) {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }

    protected function getConfigAttr($value) {
        return json_decode($value, true);
    }

    public function get($where = array(), $cache = false) {
        $data = SysConfig::alias('a')->field($this->noField, true)->cache($cache, $cache ? 10 : null)->where($where)->find();
        return $data ? $data->toArray() : array();
    }
}