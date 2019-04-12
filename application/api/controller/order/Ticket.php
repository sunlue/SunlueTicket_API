<?php
/**
 * User: xiebing
 * Date: 2019-3-19
 * Time: 15:43
 */

namespace ticket\api\controller\order;

use ticket\api\model\OrderBody;
use ticket\common\controller\Api;

class Ticket extends Api {
    private $orderBodyModel;

    public function initialize() {
        parent::_init();
        parent::checkToken();
        $this->orderBodyModel = new OrderBody();
    }

    public function get() {
        $param = input('post.');
        $where = [];
        if (!empty($param['ticket_id'])) {
            $where[] = ['ticket_id', '=', $param['ticket_id']];
        }
        if (!empty($param['order_sn'])) {
            $where[] = ['order_sn', 'like', '%' . $param['order_sn'] . '%'];
        }
        if (!empty($param['is_check'])) {
            $where[] = ['is_check', '=', $param['is_check']];
        }
        if (!empty($param['state']) || (isset($param['state']) && $param['state'] == '0')) {
            $where[] = ['state', '=', $param['state']];
        }
        if (!empty($param['mobile'])) {
            $where[] = ['mobile', '=', $param['mobile']];
        }
        if (!empty($param['contact'])) {
            $where[] = ['contact', '=', $param['contact']];
        }
        if (!empty($param['card_number'])) {
            $where[] = ['card_number', '=', $param['card_number']];
        }
        $data = $this->orderBodyModel->getList($where);
        $this->ajaxReturn(0, $data);
    }

    public function member() {
        $param = input('post.');
        $where = [];
        if (!empty($param['member_id'])) {
            $where[] = ['member_id', '=', $param['member_id']];
        }
        if (!empty($param['is_check'])) {
            $where[] = ['is_check', '=', $param['is_check']];
        }
        if (!empty($param['state']) || (isset($param['state']) && $param['state'] == '0')) {
            $where[] = ['state', '=', $param['state']];
        }
        if (!empty($param['mobile'])) {
            $where[] = ['mobile', '=', $param['mobile']];
        }
        if (!empty($param['contact'])) {
            $where[] = ['contact', '=', $param['contact']];
        }
        if (!empty($param['card_number'])) {
            $where[] = ['card_number', '=', $param['card_number']];
        }
        if (!empty($param['composite'])) {
            $where[] = ['mobile|contact|card_number', '=', $param['composite']];
        }

        $data = $this->orderBodyModel->getList($where);
        $this->ajaxReturn(0, $data);
    }

    /**
     * 验票
     */
    public function check() {
        $uniqid = input('post.uniqid');
        if (empty($uniqid)) {
            $this->ajaxReturn(400, '票务标识不能为空');
        }
        $data = $this->orderBodyModel->getFind(array(
            'uniqid' => $uniqid
        ));
        if (empty($data['add_time']) || $data['state'] == 0) {
            $this->ajaxReturn(400, '此票无效');
        }
        if ($data['state'] == 2) {
            $this->ajaxReturn(400, '此票已过期');
        }
        if ($data['is_check'] != 'no') {
            $this->ajaxReturn(400, '此票已使用');
        }
        if (strtotime($data['use_date']) < strtotime(date('Y-m-d', time()))) {
            $this->ajaxReturn(400, '已过使用日期');
        }
        if (strtotime($data['use_date']) > strtotime(date('Y-m-d', time()))) {
            $this->ajaxReturn(400, '未到使用日期');
        }
        parent::editAction(array(
            'model' => $this->orderBodyModel,
            'where' => ['uniqid' => $uniqid],
            'data' => array(
                'state' => 5,
                'is_check' => 'yes',
                'check_time' => date('Y-m-d H:i:s', time())
            ),
            'text' => '验票【' . $uniqid . '】'
        ));
    }

    /**
     * 退票
     */
    public function refund() {
        $uniqid = input('post.uniqid');
        if (empty($uniqid)) {
            $this->ajaxReturn(400, '票务标识不能为空');
        }
        $data = $this->orderBodyModel->getFind(array(
            'uniqid' => $uniqid
        ));
        if ($data['is_check'] != 'no') {
            $this->ajaxReturn(400, '此票已使用');
        }
        parent::editAction(array(
            'model' => $this->orderBodyModel,
            'where' => ['uniqid' => $uniqid],
            'data' => ['state' => 3],
            'text' => '取消【' . $uniqid . '】'
        ));
    }

    /**
     * 删除
     */

    public function remove() {
        $uniqid = input('post.uniqid');
        if (empty($uniqid)) {
            $this->ajaxReturn(400, '票务标识不能为空');
        }
        $where['uniqid'] = $uniqid;
        $data = $this->orderBodyModel->getFind($where);
        if (!empty($data) && $data['is_check'] != 'no') {
            $this->ajaxReturn(400, '此票已使用');
        }
        parent::delAction(array(
            'model' => $this->orderBodyModel,
            'where' => $where,
            'text' => '删除【' . $uniqid . '】'
        ));
    }

}