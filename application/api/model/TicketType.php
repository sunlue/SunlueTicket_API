<?php
/**
 * User: xiebing
 * Date: 2019-3-7
 * Time: 17:46
 */

namespace ticket\api\model;

use ticket\common\model\Common;

class TicketType extends Common {
    protected $pk = 'uniqid';
    protected $insert = [
        'uniqid'
    ];

    protected function setUniqidAttr() {
        return uniqid();
    }

    public function getAll($where = array()) {
        $data = TicketType::alias('a')->field('is_del,last_modify_time', true)->where($where)->select();
        return $data ? $data->toArray() : array();
    }

    public function getTicket($where = array()) {
        $data = TicketType::alias('a')->field('is_del,last_modify_time', true)->cache(true,60)->where($where)->select()->each(function ($item, $key) {
            $item->ticket = TicketList::field('is_del,last_modify_time', true)->cache(true,60)->where('type', $item['uniqid'])->select()->each(function ($ticket){
                $ticket->todayPrice = TicketList::getPrice($ticket['id'],date('Y-m-d',time()));
                $ticket->know = TicketKnow::field($this->noField, true)->where('uniqid', $ticket['know_id'])->find();
                if (!empty($ticket['thumb'])) {
                    $thumbImg = self::getCommonImg(array('_hash' => $ticket['thumb']), 'url');
                    $ticket['thumbHash'] = $ticket['thumb'];
                    $ticket['thumb'] = $thumbImg['url'];
                }
            });
        });
        return $data ? $data->toArray() : array();
    }
}