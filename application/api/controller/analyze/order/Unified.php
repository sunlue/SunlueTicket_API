<?php
/**
 * User: xiebing
 * Date: 2019-4-19
 * Time: 9:32
 */
namespace ticket\api\controller\analyze\order;


use Think\Db;
use ticket\api\controller\analyze\Total;
use ticket\common\controller\Api;

class Unified extends Api {
    public function initialize() {
//        parent::_init();
//        parent::checkToken();
    }
    public function get(){
        $beginDate=date('Y-m-01', strtotime(date("Y-m-d")));
        $afterDate=date('Y-m-d', strtotime("$beginDate +1 month -1 day"));
        $countSql=Db::name('order_list')->field('count(*) as total,date')->group('date')->fetchSql()->select();
        $order_total=Total::tempNumber('count',$beginDate,$afterDate,$countSql);

        $countSql=Db::name('order_refund')->field('count(*) as total,DATE_FORMAT(`add_time`,\'%Y-%m-%d\') as `date`')
            ->group('DATE_FORMAT(`add_time`,\'%Y-%m-%d\'),order_sn')->fetchSql()->select();
        $order_refund=Total::tempNumber('count',$beginDate,$afterDate,$countSql);

        $countSql=Db::name('order_body')->field('count(*) as total,DATE_FORMAT(`add_time`,\'%Y-%m-%d\') as `date`')
            ->group('DATE_FORMAT(`add_time`,\'%Y-%m-%d\')')->fetchSql()->select();
        $ticket_total=Total::tempNumber('count',$beginDate,$afterDate,$countSql);

        $countSql=Db::name('order_body')->field('count(*) as total,DATE_FORMAT(`check_time`,\'%Y-%m-%d\') as `date`')
            ->group('DATE_FORMAT(`check_time`,\'%Y-%m-%d\')')->fetchSql()->select();
        $ticket_check=Total::tempNumber('count',$beginDate,$afterDate,$countSql);

        $data = array(
            'date' => array_column($order_total,'date'),
            'order_total'=>array_column($order_total,'value'),
            'order_refund'=>array_column($order_refund,'value'),
            'ticket_total'=>array_column($ticket_total,'value'),
            'ticket_refund'=>array_column($order_refund,'value'),
            'ticket_check'=>array_column($ticket_check,'value')
        );
        $this->ajaxReturn(0, $data);
    }
}