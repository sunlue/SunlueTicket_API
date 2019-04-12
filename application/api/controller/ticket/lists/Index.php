<?php
/**
 * User: xiebing
 * Date: 2019-3-8
 * Time: 16:20
 */

namespace ticket\api\controller\ticket\lists;

use Think\Db;
use think\facade\Hook;
use ticket\api\model\TicketList;
use ticket\api\model\TicketPrice;
use ticket\common\controller\Api;

class Index extends Api {
    private $ticketListModel;
    private $ticketPriceModel;

    public function initialize() {
        parent::_init();
        parent::checkToken();
        $this->ticketListModel = new TicketList();
        $this->ticketPriceModel = new TicketPrice();
    }

    public function set() {
        $param = input('post.');
        $check = $this->validate($param, 'ticketList.set');
        if ($check !== true) {
            $this->ajaxReturn(400, $check);
        }
        $price = $param['price'];
        Db::startTrans();
        parent::addAction(array(
            'model' => $this->ticketListModel,
            'data' => $param,
            'text' => '添加票务'
        ), function ($result) use ($price, $param) {
            $priceArr = $this->setPrice($price, $result[1]['id']);
            if (!empty($priceArr)) {
                debug('begin');
                try {
                    $resPrice = $this->ticketPriceModel->allowField(true)->saveAll($priceArr);
                    $resultPRICE = array(
                        $resPrice ? 0 : 400,
                        $resPrice ? $this->ticketPriceModel->getData() : '价格设置失败'
                    );
                    Db::commit();
                } catch (\exception $e) {
                    $resultPRICE = array(500, '发生异常,价格设置失败');
                    $abort = $e->getMessage();
                    Db::rollback();
                }
                debug('end');
                Hook::listen('sql', array(
                    'state' => $resultPRICE[0] == 500 ? 2 : 1,
                    'sql' => $this->ticketPriceModel->getLastSql(),
                    'abort' => empty($abort) ? '' : $abort,
                    'text' => '价格设置-' . $param['name'],
                    'type' => 'create',
                    'time' => debug('begin', 'end', 6) . '秒',
                ));
                $this->ajaxReturn($result[0], $result[1]);
            }
            Db::commit();
            $this->ajaxReturn($result[0], $result[1]);
        });
    }

    public function edit() {
        $param = input('post.');
        $check = $this->validate($param, 'ticketList.edit');
        if ($check !== true) {
            $this->ajaxReturn(400, $check);
        }
        $where['id'] = $param['id'];
        unset($param['id']);
        parent::editAction(array(
            'model' => $this->ticketListModel,
            'data' => $param,
            'where' => $where,
            'text' => '修改票务[' . $param['name'] . ']'
        ));
    }

    public function remove() {
        $uniqid = input('post.id');
        if (empty($uniqid)) {
            $this->ajaxReturn(400, '参数异常');
        }
        parent::delAction(array(
            'model' => $this->ticketListModel,
            'where' => $uniqid,
            'text' => '删除票务'
        ));
    }

    public function get() {
        $param = input('post.');
        $where = [];
        if (!empty($param['type'])) {
            $where[] = ['a.type', '=', $param['type']];
        }
        if (!empty($param['name'])) {
            $where[] = ['a.name', 'like', '%' . $param['name'] . '%'];
        }
        if (!empty($param['hot']) || isset($param['hot'])) {
            $hot = $param['hot'] === 'true' ? 'yes' : ($param['hot'] == 'yes' ? 'yes' : 'no');
            $where[] = ['a.hot', '=', $hot];
        }
        if (!empty($param['recom']) || isset($param['recom'])) {
            $recom = $param['recom'] === 'true' ? 'yes' : ($param['recom'] == 'yes' ? 'yes' : 'no');
            $where[] = ['a.recom', '=', $recom];
        }
        if (!empty($param['top']) || isset($param['top'])) {
            $top = $param['top'] === 'true' ? 'yes' : ($param['top'] == 'yes' ? 'yes' : 'no');
            $where[] = ['a.top', '=', $top];
        }
        if (!empty($param['shelves']) || isset($param['shelves'])) {
            $shelves = $param['shelves'] === 'true' ? 'yes' : ($param['shelves'] == 'yes' ? 'yes' : 'no');
            $where[] = ['a.shelves', '=', $shelves];
        }
        $data = $this->ticketListModel->getList($where);
        $this->ajaxReturn(0, $data);
    }

    public function all() {
        $param = input('post.');
        $where = [];
        if (!empty($param['type'])) {
            $where[] = ['a.type', '=', $param['type']];
        }
        if (!empty($param['name'])) {
            $where[] = ['a.name', 'like', '%' . $param['name'] . '%'];
        }
        if (!empty($param['hot']) || isset($param['hot'])) {
            $hot = $param['hot'] === 'true' ? 'yes' : ($param['hot'] == 'yes' ? 'yes' : 'no');
            $where[] = ['a.hot', '=', $hot];
        }
        if (!empty($param['recom']) || isset($param['recom'])) {
            $recom = $param['recom'] === 'true' ? 'yes' : ($param['recom'] == 'yes' ? 'yes' : 'no');
            $where[] = ['a.recom', '=', $recom];
        }
        if (!empty($param['top']) || isset($param['top'])) {
            $top = $param['top'] === 'true' ? 'yes' : ($param['top'] == 'yes' ? 'yes' : 'no');
            $where[] = ['a.top', '=', $top];
        }
        if (!empty($param['shelves']) || isset($param['shelves'])) {
            $shelves = $param['shelves'] === 'true' ? 'yes' : ($param['shelves'] == 'yes' ? 'yes' : 'no');
            $where[] = ['a.shelves', '=', $shelves];
        }
        $data = $this->ticketListModel->getAll($where);
        $this->ajaxReturn(0, $data);
    }

    public function attr() {
        $param = input('post.');
        if (empty($param['id'])) {
            $this->ajaxReturn(400, '修改主键异常');
        }
        if (empty($param['type'])) {
            $this->ajaxReturn(400, '属性异常');
        }
        if (empty($param['value'])) {
            $this->ajaxReturn(400, '属性值异常');
        }
        try {
            $this->ticketListModel->where('id', $param['id'])->setField($param['type'], $param['value']);
            $this->ajaxReturn(0);
        } catch (\exception $e) {
            $this->ajaxReturn(400, $e->getMessage());
        }
    }

    public function details(){
        $id=input('post.uniqid');
        if (empty($id)){
            $this->ajaxReturn(400, '主键异常');
        }
        $data=$this->ticketListModel->getFind(array(
            'id'=>$id
        ));
        $this->ajaxReturn(0,$data);
    }


    /**
     * 设置价格
     * @param $priceArr
     * @param $room
     * @return array
     */
    public function setPrice($priceArr, $ticket) {
        $money = [];
        if (!empty($priceArr['date']) && !empty($priceArr['date']['start']) && !empty($priceArr['date']['end'])) {
            $start = date('Ymd', strtotime($priceArr['date']['start']));
            $end = date('Ymd', strtotime($priceArr['date']['end']));
            $day = abs(strtotime($end) - strtotime($start)) / 86400;
            for ($i = 0; $i <= $day; $i++) {
                $date = date('Ymd', strtotime($start) + (86400 * $i));
                $money[$date] = array(
                    'ticket' => $ticket,
                    'date' => $date,
                    'cost' => $priceArr['date']['cost'],
                    'profit' => $priceArr['date']['profit'],
                    'remark' => $priceArr['date']['remark'],
                    'number' => $priceArr['date']['number']
                );
            }
        }
        if (!empty($priceArr['week']) && !empty($priceArr['week']['start']) && !empty($priceArr['week']['end'])) {
            $start = date('Ymd', strtotime($priceArr['week']['start']));
            $end = date('Ymd', strtotime($priceArr['week']['end']));
            $day = abs(strtotime($end) - strtotime($start)) / 86400;
            for ($j = 0; $j <= $day; $j++) {
                $date = date('Ymd', strtotime($start) + (86400 * $j));
                if (in_array(date('N', strtotime($date)), $priceArr['week']['week'])) {
                    $money[$date] = array(
                        'ticket' => $ticket,
                        'date' => $date,
                        'cost' => $priceArr['week']['cost'],
                        'profit' => $priceArr['week']['profit'],
                        'remark' => $priceArr['week']['remark'],
                        'number' => $priceArr['week']['number'],
                    );
                }
            }
        }
        if (!empty($priceArr['day']['day'])) {
            $dayArr = gettype($priceArr['day']['day']) == 'string' ? explode(',', $priceArr['day']['day']) : $priceArr['day']['day'];
            foreach ($dayArr as $day) {
                if (!$day) continue;
                $money[date('Ymd', strtotime($day))] = array(
                    'ticket' => $ticket,
                    'date' => date('Ymd', strtotime($day)),
                    'cost' => $priceArr['day']['cost'],
                    'profit' => $priceArr['day']['profit'],
                    'remark' => $priceArr['day']['remark'],
                    'number' => $priceArr['day']['number'],
                );
            }
        }
        return $money;
    }

    public function getPrice() {
        $param = input('post.');
        $where = [];
        $ticket = [];
        if (!empty($param['ticket'])) {
            $where[] = ['ticket', 'eq', $param['ticket']];
            $ticket = $this->ticketListModel->getFind(['id' => $param['ticket']]);
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
        $price = $this->ticketPriceModel->getAll($where);
        $this->ajaxReturn(0, array('ticket' => $ticket, 'price' => $price));
    }


}