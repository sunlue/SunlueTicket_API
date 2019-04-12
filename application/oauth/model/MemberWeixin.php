<?php
/**
 * User: xiebing
 * Date: 2019-4-3
 * Time: 14:22
 */

namespace ticket\oauth\model;

use ticket\common\model\Common;

class MemberWeixin extends Common {
    protected $pk = 'uniqid';

    protected $insert = array(
        'uniqid',
        'add_time',
    );

    protected function setUniqidAttr() {
        return uniqid('mpweixin_');
    }

    protected function setAddTimeAttr() {
        return date('Y-m-d H:i:s', time());
    }

    public function login($where = array()) {
        $data = MemberWeixin::alias('a')
            ->field($this->noField, true)
            ->where($where)->find();
        return $data ? $data->toArray() : array();
    }
}