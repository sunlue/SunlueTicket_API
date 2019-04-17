<?php
/**
 * User: xiebing
 * Date: 2019-4-17
 * Time: 16:04
 */

namespace ticket\api\controller\analyze\access;

use ticket\common\controller\Api;

class Referer extends Api {
    public function initialize() {
        parent::_init();
        parent::checkToken();
    }

    public function get() {

        $data = array(
            'date' => ["微信公众号", "微信小程序", "Android", "IOS", "WAP"],
            'data' => array(
                array(
                    'name' => '微信公众号',
                    'value' => rand(10, 100)
                ),
                array(
                    'name' => '微信小程序',
                    'value' => rand(10, 100)
                ),
                array(
                    'name' => 'Android',
                    'value' => rand(10, 100)
                ),
                array(
                    'name' => 'IOS',
                    'value' => rand(10, 100)
                ),
                array(
                    'name' => 'WAP',
                    'value' => rand(10, 100)
                )
            )
        );
        $this->ajaxReturn(0, $data);
    }
}