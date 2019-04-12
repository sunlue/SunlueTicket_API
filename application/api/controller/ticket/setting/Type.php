<?php
/**
 * User: xiebing
 * Date: 2019-3-7
 * Time: 17:40
 */

namespace ticket\api\controller\ticket\setting;

use ticket\api\model\TicketType;
use ticket\common\controller\Api;

class Type extends Api {
    private $ticketTypeModel;

    public function initialize() {
        parent::_init();
        parent::checkToken();
        $this->ticketTypeModel = new TicketType();
    }

    public function set() {
        $param = input('post.');
        if (isset($param['uniqid']) && !empty($param['uniqid'])) {
            $where['uniqid'] = $param['uniqid'];
            unset($param['uniqid']);
            parent::editAction(array(
                'model' => $this->ticketTypeModel,
                'data' => $param,
                'where' => $where,
                'text' => '修改票务类型' . $where['uniqid']
            ));
        } else {
            parent::addAction(array(
                'model' => $this->ticketTypeModel,
                'data' => $param,
                'text' => '添加票务类型'
            ));
        }
    }

    public function get() {
        $data = $this->ticketTypeModel->getAll();
        $this->ajaxReturn(0, $data);
    }

    public function remove() {
        $uniqid = input('post.uniqid');
        if (empty($uniqid)) {
            $this->ajaxReturn(400, '参数异常');
        }
        parent::delAction(array(
            'model' => $this->ticketTypeModel,
            'where' => $uniqid,
            'text' => '删除票务类型'
        ));
    }

    public function enable() {
        $enable = input('post.enable');
        $uniqid = input('post.uniqid');
        parent::editAction(array(
            'model' => $this->ticketTypeModel,
            'data' => ['enable' => $enable],
            'where' => ['uniqid' => $uniqid],
            'text' => ($enable == 'yes' ? '启用' : '禁用')
        ));
    }

    public function ticket(){
        $data = $this->ticketTypeModel->getTicket();
        $this->ajaxReturn(0, $data);
    }

}