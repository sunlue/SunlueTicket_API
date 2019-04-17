<?php
/**
 * User: xiebing
 * Date: 2019-4-16
 * Time: 14:55
 */
namespace ticket\api\controller\analyze;
use think\Db;
use ticket\common\controller\Api;

class Order extends Api{

    protected function initialize() {
//        parent::_init();
//        parent::checkToken();
    }
    public function index(){


        $data=Db::name('orderBody')->select();


        dump($data);


    }
}