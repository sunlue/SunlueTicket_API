<?php
/**
 * User: xiebing
 * Date: 2019-3-15
 * Time: 14:04
 */

namespace ticket\api\model;

use ticket\common\model\Common;

class OrderBody extends Common {
    protected $pk = 'uniqid';
    protected $insert = array(
        'is_check' => 'no',
        'buy_time',
        'uniqid'
    );

    protected function setBuyTimeAttr() {
        return date('Y-m-d H:i:s', time());
    }

    protected function setUniqidAttr() {
        return strtolower(uniqid(time() . '_'));
    }

    public function getList($where = array()) {
        $ticketModel=new TicketList();
        return OrderBody::field($this->noField, true)
            ->cache(true,10)->where($where)
            ->paginate(input('post.limit', null))->each(function ($item) use ($ticketModel) {
                $item['ticket'] = $ticketModel->getFind(array(
                    'id'=>$item['ticket_id']
                ));
                $item['use_price']=TicketList::getPrice($item['ticket_id'], $item['use_date']);
                $item['pay_type'] = $item['pay_type'] ? $this->payType[$item['pay_type']] : '未支付';
                $item['status'] = $this->orderBodyState[$item['state']];
        })->toArray();
    }

    public function getAll($where = array()) {
        $ticketModel=new TicketList();
        return OrderBody::field($this->noField, true)
            ->cache(true,10)->where($where)
            ->select()->each(function ($item) use ($ticketModel) {
                $item['ticket'] = $ticketModel->getFind(array(
                    'id'=>$item['ticket_id']
                ));
                $item['use_price']=TicketList::getPrice($item['ticket_id'], $item['use_date']);
                $item['pay_type'] = $item['pay_type'] ? $this->payType[$item['pay_type']] : '未支付';
                $item['status'] = $this->orderBodyState[$item['state']];
            })->toArray();
    }

    public function getFind($where = array(), $cache = false) {
        return OrderBody::field($this->noField, true)->where($where)->cache($cache, 10)->find();
    }

}





















