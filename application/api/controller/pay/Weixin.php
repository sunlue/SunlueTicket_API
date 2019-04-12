<?php
/**
 * User: xiebing
 * Date: 2019-3-18
 * Time: 15:48
 */

namespace ticket\api\controller\pay;

use Think\Db;
use ticket\common\controller\Api;

class Weixin extends Api {
    private $config;
    private $weixin;

    protected $beforeActionList = [
        'before',
    ];

    public function initialize() {
        parent::_init();
        parent::checkToken();
        $this->config = Config::obta('weixin');
    }

    protected function before() {
        if ($this->config['swtich'] != 'open') {
            $this->ajaxReturn(400, '微信支付已关闭');
        }
        $certPath[] = dirname(dirname(__DIR__));
        $certPath[] = 'paycert';
        $certPath[] = $this->config['config']['mchid'];
        $certPath = implode(DIRECTORY_SEPARATOR, $certPath);
        $config = array(
            'app_id' => $this->config['config']['appid'],
            'secret' => $this->config['config']['secret'],
            'payment' => array(
                'merchant_id' => $this->config['config']['mchid'],
                'key' => $this->config['config']['mchkey'],
                'cert_path' => $certPath . DIRECTORY_SEPARATOR . 'apiclient_cert.pem',
                'key_path' => $certPath . DIRECTORY_SEPARATOR . 'apiclient_key.pem',
                'notify_url' => url('notify', false, true, true),
            )
        );
        $this->weixin = new \EasyWeChat\Foundation\Application($config);
    }

    /**
     * 统一下单
     */
    public function unifiedorder() {
        $param = input('post.');
        $orderInfo = Db::view('order_list_view')->where('order_sn', $param['order_sn'])->cache($param['order_sn'], 60)->find();
        $trade_no = $orderInfo['trade_no'];
        if (empty($orderInfo)) {
            $this->ajaxReturn(400, '订单异常');
        } elseif (!empty($orderInfo['unifiedorder'])) {
            $c = new \ticket\api\controller\order\Index();
            $result = $c->updateTradeNo(array(
                'order_sn' => $param['order_sn']
            ));
            if ($result['code'] != 0) {
                $this->ajaxReturn(400, $result['msg']);
            }
            $trade_no = $result['data'];
        }
        $attributes = [
            'body' => '购买门票',
            'trade_type' => !empty($param['trade_type']) ? $param['trade_type'] : 'JSAPI',
            'detail' => !empty($param['pay_detail']) ? $param['pay_detail'] : 'detail',
            'out_trade_no' => $trade_no,
            'total_fee' => $orderInfo['money'] * 100,
        ];
        if ($attributes['trade_type'] == 'JSAPI') {
            if (empty($param['openid'])) {
                $this->ajaxReturn(400, '用户授权异常');
            }
            $attributes['openid'] = 'ok9y9jljolUEFK12M0im0t3zkeG4';
            //            $attributes['openid'] = $param['openid'];
        }
        $order = new \EasyWeChat\Payment\Order($attributes);
        $result = $this->weixin->payment->prepare($order);
        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS') {
            if ($attributes['trade_type'] == 'JSAPI') {
                if (input('post.js_type', 'bridge') == "sdk") {
                    $js_result = $this->weixin->payment->configForJSSDKPayment($result->prepay_id, false);
                } else {
                    $js_result = $this->weixin->payment->configForPayment($result->prepay_id, false);
                }
                $this->ajaxReturn(0, $js_result);
            } else {
                $this->ajaxReturn(0, $result->toArray());
            }
        } else if ($result->return_code != 'SUCCESS') {
            $this->ajaxReturn(400, $result->return_msg);
        } else if ($result->result_code != 'SUCCESS') {
            $this->ajaxReturn(400, $result->err_code_des);
        }
    }

    /**
     * 支付回调
     */
    public function notify() {
        $response = $this->weixin->payment->handleNotify(function ($notify, $successful) {
            $order = new \ticket\api\controller\order\Index();
            $order->notify($notify, $successful);
        });
        $response->send();
    }
}