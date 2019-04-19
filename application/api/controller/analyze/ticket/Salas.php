<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-4-19
 * Time: 17:27
 */

namespace ticket\api\controller\analyze\ticket;


use Think\Db;
use ticket\api\controller\analyze\Total;

class Salas extends Total {
    public function initialize() {
        parent::_init();
        parent::checkToken();
    }
    public function get(){
        $data = Db::name('order_body a ')
            ->field('COUNT(*),b.name,DATE_FORMAT(a.add_time,\'%Y-%m-%d\') as date,ticket_id')
            ->leftJoin('ticket_list b','a.ticket_id=b.id')
            ->group('`ticket_id`,DATE_FORMAT(a.add_time,\'%Y-%m-%d\')')->select();
        $data = array(
            'date' => array_column($data,'name'),
            'data' => $data
        );
        $this->ajaxReturn(0, $data);
    }
}