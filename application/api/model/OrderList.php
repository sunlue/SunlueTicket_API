<?php
/**
 * User: xiebing
 * Date: 2019-3-15
 * Time: 14:04
 */

namespace ticket\api\model;

use ticket\common\model\Common;

class OrderList extends Common {
    protected $pk = 'id';
    protected $insert = array(
        'add_time',
        'state' => 0,
    );

    protected function setAddTimeAttr() {
        return date('Y-m-d H:i:s', time());
    }

    protected function setTradeNoAttr($value) {
        return strtoupper(md5($value));
    }

    public function getFind($where = array(), $cache = false, $body = true) {
        $data = OrderList::field($this->noField, true)->cache($cache,30)->where($where)->find();
        if (empty($data)){return array();}
        $conditions[] = ['order_id', '=', $data['id']];
        $conditions[] = ['order_sn', '=', $data['order_sn']];
        $data['status'] = $this->orderState[$data['state']];
        $data['count'] = OrderBody::where($conditions)->count();
        $data['check'] = OrderBody::where($conditions)->where(['is_check' => 'yes'])->count();
        if ($body === true) {
            $orderBodyModel=new OrderBody();
            $item['body']=$orderBodyModel->getAll($where);
        }
        return $data ? $data->toArray() : array();
    }

    public function getAll($where = array(), $body = false) {
        $data = OrderList::field($this->noField, true)->where($where)->select();
        $data->each(function ($item) use ($body) {
            $where[] = ['order_id', '=', $item['id']];
            $where[] = ['order_sn', '=', $item['order_sn']];
            $item['status'] = $this->orderState[$item['state']];
            $item['count'] = OrderBody::where($where)->count();
            $item['check'] = OrderBody::where($where)->where(['is_check' => 'yes'])->count();
            if ($body === true) {
                $orderBodyModel=new OrderBody();
                $item['body']=$orderBodyModel->getAll($where);
            }
            unset($item['details']);
        });
        return $data ? $data->toArray() : array();
    }

    public function getList($where = array(), $body = false) {
        $data = OrderList::field($this->noField, true)->where($where)->paginate(input('post.limit', null));
        $data->each(function ($item) use ($body) {
            $where[] = ['order_id', '=', $item['id']];
            $where[] = ['order_sn', '=', $item['order_sn']];
            $item['status'] = $this->orderState[$item['state']];
            $item['count'] = OrderBody::where($where)->count();
            $item['check'] = OrderBody::where($where)->where(['is_check' => 'yes'])->count();
            if ($body === true) {
                $orderBodyModel=new OrderBody();
                $item['body']=$orderBodyModel->getAll($where);
            }
            unset($item['details']);
        });
        return $data ? $data->toArray() : array();
    }
}









