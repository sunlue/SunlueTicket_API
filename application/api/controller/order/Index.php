<?php
/**
 * User: xiebing
 * Date: 2019-3-15
 * Time: 13:58
 */

namespace ticket\api\controller\order;

use think\Db;
use ticket\api\model\OrderBody;
use ticket\api\model\OrderList;
use ticket\api\model\TicketList;
use ticket\common\controller\Api;

class Index extends Api {
    private $orderListModel;
    private $orderBodyModel;

    public function initialize() {
        parent::_init();
        parent::checkToken();
        $this->orderListModel = new OrderList();
        $this->orderBodyModel = new OrderBody();
    }

    /**
     * 生成订单号
     * @return string
     */
    protected function orderSn() {
        $utimestamp = microtime(true);
        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);
        $utimestamp = date(preg_replace('`(?<!\\\\)u`', $milliseconds, 'u'), $timestamp);
        return 'T' . date('ymdHis', time()) . str_pad($utimestamp, 6, "0", STR_PAD_LEFT) . strtoupper(uniqid());
    }


    /**
     * 更新交易号
     */
    public function updateTradeNo($where) {
        $trade_no = $this->orderSn();
        Db::startTrans();
        try {
            $this->orderListModel->save(array(
                'trade_no' => $trade_no
            ), $where);
            $result = $this->orderListModel->getData();
            $this->orderBodyModel->save(array(
                'pay_sn' => $result['trade_no']
            ), $where);
            Db::commit();
            return array(
                'code' => 0,
                'data' => $result['trade_no']
            );
        } catch (\exception $e) {
            Db::rollback();
            return array(
                'code' => 400,
                'msg' => $e->getMessage()
            );
        }
    }

    /**
     * 生成一个订单
     */
    public function set() {
        $param = input('post.');
        $param['order_sn'] = $this->orderSn();
        $param['trade_no'] = $param['order_sn'];
        $check = $this->validate($param, 'orderList.set');
        if ($check !== true) {
            $this->ajaxReturn(400, $check);
        }
        $ticket = $param['ticket'];
        if (count($ticket) == count($ticket, 1)) {
            if ($this->validate($ticket, 'orderBody.set') !== true) {
                $this->ajaxReturn(400, $check);
            }
            $ticket = [$param['ticket']];
        } else {
            foreach ($ticket as $k => $t) {
                $t['date'] = $param['date'];
                if ($this->validate($t, 'orderBody.set') !== true) {
                    $this->ajaxReturn(400, $check);
                }
            }
        }

        Db::startTrans();
        parent::addAction(array(
            'model' => $this->orderListModel,
            'data' => $param,
            'text' => ''
        ), function ($result) use ($ticket) {
            if ($result[0]['code'] == 0) {
                $k = 0;
                $ticketBody = [];
                foreach ($ticket as $t) {
                    for ($number = 0; $number < $t['number']; $number++) {
                        $ticketBody[$k]['ticket_id'] = $t['ticket_id'];
                        $ticketBody[$k]['use_date'] = $result[1]['date'];
                        $ticketBody[$k]['member_id'] = $result[1]['member_id'];
                        $ticketBody[$k]['contact'] = $result[1]['contact'];
                        $ticketBody[$k]['mobile'] = $result[1]['mobile'];
                        $ticketBody[$k]['note'] = isset($result[1]['note']) ? $result[1]['note'] : '';
                        $ticketBody[$k]['order_id'] = $result[1]['id'];
                        $ticketBody[$k]['order_sn'] = $result[1]['order_sn'];
                        $ticketBody[$k]['pay_sn'] = $result[1]['trade_no'];
                        $ticketBody[$k]['pay_money'] = TicketList::getPrice($t['ticket_id'], $result[1]['date']);
                        $k += 1;
                    }
                }
                try {
                    $ticketRes = $this->orderBodyModel->saveAll($ticketBody);
                    $resTicket = array_map(function ($item) {
                        unset($item['last_modify_time']);
                        return $item;
                    }, $ticketRes->toArray());
                    $result[1]['ticket'] = $resTicket;
                    Db::commit();
                } catch (\exception $e) {
                    Db::rollback();
                    $this->ajaxReturn(400, $e->getMessage());
                }
            } else {
                Db::rollback();
            }
            $this->ajaxReturn($result[0], $result[1]);
        });
    }

    /**
     * 获取订单
     */
    public function get() {
        $param = input('post.');
        $where = [];
        if (!empty($param['date'])) {
            $where[] = ['date', '=', $param['date']];
        }
        if (!empty($param['order_sn'])) {
            $where[] = ['order_sn', 'like', '%' . $param['order_sn'] . '%'];
        }
        if (!empty($param['user'])) {
            $where[] = ['contact|mobile', 'like', '%' . $param['user'] . '%'];
        }
        if (!empty($param['state']) || (isset($param['state']) && $param['state'] == '0')) {
            $where[] = ['state', '=', $param['state']];
        }
        $data = $this->orderListModel->getList($where, !empty($param['body']) ?: true);
        $this->ajaxReturn(0, $data);
    }

    /**
     * 取消订单
     */
    public function cancel() {
        $param = input('post.');
        $check = $this->validate($param, 'orderList.cancel');
        if ($check !== true) {
            $this->ajaxReturn(400, $check);
        }
        parent::editAction(array(
            'model' => $this->orderListModel,
            'where' => $param,
            'data' => ['state' => 4],
            'text' => '取消订单【' . $param['order_sn'] . '】'
        ));
    }

    /**
     * 订单详情
     */
    public function detail() {
        $param = input('post.');
        $where = [];
        if (!empty($param['id'])) {
            $where[] = ['id', '=', $param['id']];
        }
        if (!empty($param['order_sn'])) {
            $where[] = ['order_sn', '=', $param['order_sn']];
        }
        $data = $this->orderListModel->getFind($where, true, !empty($param['body']) ?: true);
        $this->ajaxReturn(0, $data);
    }

    /**
     * 删除订单
     */
    public function remove() {
        $param = input('post.');
        $check = $this->validate($param, 'orderList.cancel');
        if ($check !== true) {
            $this->ajaxReturn(400, $check);
        }
        parent::delAction(array(
            'model' => $this->orderListModel,
            'where' => $param,
            'text' => '删除订单【' . $param['order_sn'] . '】'
        ));
    }


    /**
     * 订单支付后处理
     */
    public function notify($notify, $successful) {
        $where['trade_no'] = $notify->out_trade_no;
        $orderInfo = $this->orderListModel->getFind($where);
        $log['notify'] = $notify->toArray();
        if (empty($orderInfo)) {
            $log['err_msg'] = '订单[' . $where['trade_no'] . ']不存在';
            trace($log, 'pay');
            exit();
        }
        if ($orderInfo['state'] == 1) {
            $log['err_msg'] = '订单[' . $where['trade_no'] . ']重复支付';
            trace($log, 'pay');
            exit();
        }
        if ($successful) {
            $payResult = array('state' => 1);
        } else {
            $payResult = array('state' => 2);
        }
        Db::startTrans();
        try {
            $this->orderListModel->save($payResult, $where);
            $this->orderBodyModel->save(array(
                'pay_time' => date('Y-m-d H:i:s', time()),
                'add_time' => date('Y-m-d H:i:s', time()),
                'pay_user' => $notify->openid,
                'pay_type' => 'weixin',
                'state' => 1
            ), array(
                'pay_sn' => $where['trade_no']
            ));
            Db::commit();
        } catch (\exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

}