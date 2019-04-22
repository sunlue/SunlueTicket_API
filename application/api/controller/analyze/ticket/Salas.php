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
        //        parent::_init();
        //        parent::checkToken();
    }

    public function get() {

        $beginDate = date('Y-m-01', strtotime(date("Y-m-d")));
        $afterDate = date('Y-m-d', strtotime("$beginDate +1 month -1 day"));

        $data = Db::name('order_body a ')->field('COUNT(*) as count,b.name,b.type,DATE_FORMAT(a.add_time,\'%Y-%m-%d\') as date,ticket_id')->leftJoin('ticket_list b', 'a.ticket_id=b.id')->where('DATE_FORMAT(a.`add_time`,\'%Y-%m-%d\') BETWEEN \'' . $beginDate . '\' AND \'' . $afterDate . '\'')->group('`ticket_id`,DATE_FORMAT(a.add_time,\'%Y-%m-%d\')')->select();
        //取出所有票务
        $ticket_ids = array_column($data, 'ticket_id');
        //去重所有票务
        $ticket_id = array_unique($ticket_ids);
        sort($ticket_id);
        $yAxis = [];
        $legend = [];
        $series = [];
        foreach ($ticket_id as $id) {
            //取出所有票务的键
            $ticket_keys = array_keys($ticket_ids, $id);
            $legend[] = $data[$ticket_keys[0]]['name'];
            $yAxis[$id]['type'] = 'bar';
            $yAxis[$id]['name'] = $data[$ticket_keys[0]]['name'];
            $ticket_type=array_keys( array_column($data,'type'),$data[$ticket_keys[0]]['type']);
            $yAxis[$id]['stack'] = $data[$ticket_type[0]]['name'];

            foreach ($ticket_keys as $row) {
                $ticket_data[] = $data[$row];
            }
            foreach (get_date_from_range($beginDate, $afterDate, '-') as $date) {
                $ticket_key = array_keys(array_column($ticket_data, 'date'), $date);
                if (!empty($ticket_key)) {
                    $yAxis[$id]['data'][] = $data[$ticket_key[0]]['count'];
                } else {
                    $yAxis[$id]['data'][] = 0;
                }
            }
            $series[] = $yAxis[$id];
        }
        $data = array(
            'legend' => $legend,
            'date' => get_date_from_range($beginDate, $afterDate, '-'),
            'series' => $series,
        );
        $this->ajaxReturn(0, $data);
    }
}