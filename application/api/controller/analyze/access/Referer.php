<?php
/**
 * User: xiebing
 * Date: 2019-4-17
 * Time: 16:04
 */

namespace ticket\api\controller\analyze\access;

use Think\Db;
use ticket\common\controller\Api;

class Referer extends Api {
    public function initialize() {
        parent::_init();
        parent::checkToken();
    }

    public function get() {
        $data = Db::name('access')->field('count(*) as value,`type`')->group('`type`')->select();
        $pcKey = array_search('pc', array_column($data, 'type'));
        $wapKey = array_search('wap', array_column($data, 'type'));
        $mpWeixinKey = array_search('mp_weixin', array_column($data, 'type'));
        $mpAlipayKey = array_search('mp_alipay', array_column($data, 'type'));
        $mpBaiduKey = array_search('mp_baidu', array_column($data, 'type'));
        $androidKey = array_search('android', array_column($data, 'type'));
        $iosKey = array_search('ios', array_column($data, 'type'));
        $weixinPublicKey = array_search('weixin_public', array_column($data, 'type'));
        $data = array(
            'date' => ["电脑端", "微信公众号", "微信小程序", "支付宝小程序", "百度小程序", "Android", "IOS", "WAP"],
            'data' => array(
                array(
                    'name' => '电脑端',
                    'value' => ($pcKey || $pcKey==0) ? $data[$pcKey]['value'] : 0
                ),
                array(
                    'name' => '微信公众号',
                    'value' => $weixinPublicKey || $pcKey==0 ? $data[$weixinPublicKey]['value'] : 0
                ),
                array(
                    'name' => '微信小程序',
                    'value' => $mpWeixinKey || $pcKey==0 ? $data[$mpWeixinKey]['value'] : 0
                ),
                array(
                    'name' => '支付宝小程序',
                    'value' => $mpAlipayKey || $pcKey==0 ? $data[$mpAlipayKey]['value'] : 0
                ),
                array(
                    'name' => '百度小程序',
                    'value' => $mpBaiduKey || $pcKey==0 ? $data[$mpBaiduKey]['value'] : 0
                ),

                array(
                    'name' => 'Android',
                    'value' => $androidKey || $pcKey==0 ? $data[$androidKey]['value'] : 0
                ),
                array(
                    'name' => 'IOS',
                    'value' => $iosKey || $pcKey==0 ? $data[$iosKey]['value'] : 0
                ),
                array(
                    'name' => 'WAP',
                    'value' => $wapKey || $pcKey==0 ? $data[$wapKey]['value'] : 0
                )
            )
        );
        $this->ajaxReturn(0, $data);
    }
}