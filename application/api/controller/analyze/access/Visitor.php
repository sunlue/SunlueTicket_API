<?php
/**
 * User: Administrator
 * Date: 2019-4-17
 * Time: 13:36
 */

namespace ticket\api\controller\analyze\access;

use ticket\common\controller\Api;

class visitor extends Api {
    public function get() {
        $date = [];
        $ip = [];
        $pv = [];
        $uv = [];
        for ($i = 0; $i < 7; $i++) {
            $date[] = date('Y-m-d', strtotime('+' . $i . ' days'));
            $ip[] = rand(1, 100);
            $uv[] = rand(1, 99);
            $pv[] = rand(20, 199);
        }
        $data = array(
            'date' => $date,
            'ip' => $ip,
            'uv' => $uv,
            'pv' => $pv,

            'week'=>["周一", "周二", "周三", "周四", "周五", "周六", "周日"],
            'total'=>$ip,
            'refund'=>$uv,
            'order_total'=>$ip,
            'order_refund'=>$pv,

        );
        $this->ajaxReturn(0, $data);
    }
}