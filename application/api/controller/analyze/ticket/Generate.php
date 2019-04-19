<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-4-19
 * Time: 16:48
 */

namespace ticket\api\controller\analyze\ticket;


use Think\Db;
use ticket\api\controller\analyze\Total;

class Generate extends Total {
    public function initialize() {
        parent::_init();
        parent::checkToken();
    }
    public function get(){
        $data = Db::name('order_body a ')
            ->leftJoin('ticket_list b','a.ticket_id=b.id')
            ->field('count(*) as value,b.name')->group('`ticket_id`')->select();
        $data = array(
            'date' => array_column($data,'name'),
            'data' => $data
        );
        $this->ajaxReturn(0, $data);
    }
}