<?php
/**
 * User: xiebing
 * Date: 2019-3-13
 * Time: 17:53
 */

namespace ticket\api\controller\ticket\price;

use ticket\api\model\TicketPrice;
use ticket\common\controller\Api;

class Index extends Api {
    private $priceModel;

    public function initialize() {
        parent::_init();
        parent::checkToken();
        $this->priceModel = new TicketPrice();
    }

    public function set() {
        $param = input('post.');
        $check = $this->validate($param, 'ticketPrice.set');
        if ($check !== true) {
            $this->ajaxReturn(400, $check);
        }
        $param['date'] = date('Ymd', strtotime($param['date']));
        $where = array(
            'ticket' => $param['ticket'],
            'date' => $param['date']
        );
        if ($this->priceModel->getFind($where)) {
            unset($param['date']);
            unset($param['ticket']);
            parent::editAction(array(
                'model' => $this->priceModel,
                'data' => $param,
                'where' => $where,
                'text' => '修改票务价格'
            ));
        } else {
            parent::addAction(array(
                'model' => $this->priceModel,
                'data' => $param,
                'text' => '设置票价'
            ), function ($result) {
                if (is_array($result[1])) {
                    $result[1]['date'] = date('Y-m-d', strtotime($result[1]['date']));
                }
                $this->ajaxReturn($result[0], $result[1]);
            });
        }
    }

    public function get() {
        $param = input('post.');
        $where = [];
        if (!empty($param['ticket'])) {
            $where[] = ['ticket', 'eq', $param['ticket']];
        }
        if (!empty($param['start'])) {
            $where[] = ['date', 'egt', date('Ymd',strtotime($param['start']))];
        }
        if (!empty($param['end'])) {
            $where[] = ['date', 'elt', date('Ymd',strtotime($param['end']))];
        }
        if (!empty($param['start']) && !empty($param['end'])) {
            $where[] = ['date', ['egt', date('Ymd',strtotime($param['start']))], ['elt', date('Ymd',strtotime($param['end']))]];
        }
        $price = $this->priceModel->getAll($where);
        $this->ajaxReturn(0,$price);
    }


}