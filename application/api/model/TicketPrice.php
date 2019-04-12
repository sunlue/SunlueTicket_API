<?php
/**
 * User: xiebing
 * Date: 2019-3-7
 * Time: 17:46
 */

namespace ticket\api\model;

use ticket\common\model\Common;

class TicketPrice extends Common {

    public function getAll($where = array()) {
        $data = TicketPrice::alias('a')->field('is_del,last_modify_time,ticket', true)->cache(true,60)
            ->where($where)->select()->each(function ($item) {
            $item->date = date('Y-m-d', strtotime($item['date']));
        });
        return $data ? $data->toArray() : array();
    }

    public function getFind($where = array()) {
        $data = TicketPrice::alias('a')->where($where)->find();
        return $data ? $data->toArray() : array();
    }
}