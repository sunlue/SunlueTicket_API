<?php
/**
 * User: xiebing
 * Date: 2019-3-27
 * Time: 15:58
 */

namespace ticket\api\controller\ticket\setting;

use ticket\api\model\TicketKnow;
use ticket\common\controller\Api;

class Know extends Api {
    private $ticketKnowModel;

    public function initialize() {
        parent::_init();
        parent::checkToken();
        $this->ticketKnowModel = new TicketKnow();
    }


    public function get() {
        $params = input('post.');
        $where = [];
        if (!empty($params['book_type'])) {
            $where[] = ['book_type', '=', $params['book_type']];
        }
        if (!empty($params['book_day'])) {
            $where[] = ['book_day', '=', $params['book_day']];
        }
        if (!empty($params['aging_type'])) {
            $where[] = ['aging_type', '=', $params['aging_type']];
        }
        if (!empty($params['aging_day'])) {
            $where[] = ['aging_day', '=', $params['aging_day']];
        }
        if (!empty($params['start_time'])) {
            $where[] = ['start_time', '>=', $params['start_time']];
        }
        if (!empty($params['end_time'])) {
            $where[] = ['end_time', '<=', $params['end_time']];
        }
        $data = $this->ticketKnowModel->getList($where);
        $this->ajaxReturn(0, $data);
    }

    public function all() {
        $params = input('post.');
        $where = [];
        if (!empty($params['book_type'])) {
            $where[] = ['book_type', '=', $params['book_type']];
        }
        if (!empty($params['book_day'])) {
            $where[] = ['book_day', '=', $params['book_day']];
        }
        if (!empty($params['aging_type'])) {
            $where[] = ['aging_type', '=', $params['aging_type']];
        }
        if (!empty($params['aging_day'])) {
            $where[] = ['aging_day', '=', $params['aging_day']];
        }
        if (!empty($params['start_time'])) {
            $where[] = ['start_time', '>=', $params['start_time']];
        }
        if (!empty($params['end_time'])) {
            $where[] = ['end_time', '<=', $params['end_time']];
        }
        $data = $this->ticketKnowModel->getAll($where);
        $this->ajaxReturn(0, $data);
    }

    public function set() {
        $params = input('post.');
        $check = $this->validate($params, 'ticketKnow.set');
        if ($check !== true) {
            $this->ajaxReturn(400, $check);
        }
        parent::addAction(array(
            'model' => $this->ticketKnowModel,
            'text' => '添加票务须知',
            'data' => $params
        ));
    }

    public function edit() {
        $params = input('post.');
        $check = $this->validate($params, 'ticketKnow.edit');
        if ($check !== true) {
            $this->ajaxReturn(400, $check);
        }
        $where[] = ['uniqid', '=', $params['uniqid']];
        unset($params['uniqid']);
        parent::editAction(array(
            'where' => $where,
            'model' => $this->ticketKnowModel,
            'text' => '修改票务须知',
            'data' => $params
        ));
    }

    public function remove() {
        $params = input('post.');
        $check = $this->validate($params, 'ticketKnow.remove');
        if ($check !== true) {
            $this->ajaxReturn(400, $check);
        }
        parent::delAction(array(
            'where' => ['uniqid', '=', $params['uniqid']],
            'model' => $this->ticketKnowModel,
            'text' => '删除票务须知',
        ));
    }

}