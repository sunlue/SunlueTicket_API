<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-1-17
 * Time: 10:38
 */

namespace ticket\api\controller\analyze\access;

use ticket\api\controller\analyze\Total;

class Traffic extends Total {
    private $accessModel;
    public function initialize() {
        $this->accessModel = new \ticket\index\model\Access();
    }
    public function get(){
        $start_date = date('Y-m-d', strtotime('-7 day'));
        $end_date = date('Y-m-d', time());
        $pv=self::tempNumber('count',$start_date,$end_date,$this->accessModel->getPv('',true));
        $uv=self::tempNumber('count',$start_date,$end_date,$this->accessModel->getUv('',true));
        $ip=self::tempNumber('count',$start_date,$end_date,$this->accessModel->getIp('',true));
        $this->ajaxReturn(0,array(
            'date'=>array_column($pv,'date'),
            'pv'=>array_column($pv,'value'),
            'uv'=>array_column($uv,'value'),
            'ip'=>array_column($ip,'value'),
        ));
    }
}