<?php
/**
 * User: xiebing
 * Date: 2019-3-27
 * Time: 16:41
 */

namespace ticket\api\model;

use ticket\common\model\Common;

class TicketKnow extends Common {


    protected $pk = "uniqid";
    protected $insert = array(
        'uniqid'
    );

    protected function setUniqidAttr() {
        return uniqid('ticketKnow_');
    }

    public function getList($where = array()) {
        $data = TicketKnow::alias('a')->field($this->noField, true)->where($where)->paginate(input('post.limit', null));
        return $data ? $data->toArray() : array();
    }

    public function getAll($where = array()) {
        $data = TicketKnow::alias('a')->field($this->noField, true)->where($where)->select();
        return $data ? $data->toArray() : array();
    }
}