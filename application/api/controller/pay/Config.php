<?php
/**
 * User: xiebing
 * Date: 2019-3-4
 * Time: 16:55
 */

namespace ticket\api\controller\pay;

use ticket\api\model\PayConfig;
use ticket\common\controller\Api;

class Config extends Api {
    private $payConfigModel;

    public function initialize() {
        parent::_init();
        parent::checkToken();
        $this->payConfigModel = new PayConfig();
    }

    /**
     * 配置获取
     */
    public function get() {
        $param = input('post.');
        $check = $this->validate($param, 'PayConfig.get');
        if ($check !== true) {
            $this->ajaxReturn(400, $check);
        }
        $config = $this->payConfigModel->get(array(
            'provider' => $param['provider']
        ));
        $this->ajaxReturn(0, $config);
    }

    /**
     * 微信支付配置
     */
    public function weixin() {
        $this->action('weixin', '微信');
    }

    /**
     * 支付宝配置
     */
    public function alipay() {
        $this->action('alipay', '支付宝');
    }

    /**
     * 配置操作
     * @param $provider
     * @param $text
     */
    public function action($provider, $text) {
        $param = input('post.');
        $data['swtich'] = $param['swtich'];
        unset($param['swtich']);
        $data['config'] = $param;
        $check = $this->validate($data, 'PayConfig.edit');
        if ($check !== true) {
            $this->ajaxReturn(400, $check);
        }
        $data['provider'] = $provider;
        $config = $this->payConfigModel->get(array(
            'provider' => $provider
        ));
        if (empty($config)) {
            parent::addAction(array(
                'model' => $this->payConfigModel,
                'data' => $data,
                'text' => '添加' . $text . '配置'
            ));
        } else {
            parent::editAction(array(
                'model' => $this->payConfigModel,
                'data' => $data,
                'text' => '修改' . $text . '配置',
                'where' => ['provider' => $provider]
            ));
        }
    }

    public static function obta($type) {
        $payConfigModel = new PayConfig();
        $config = $payConfigModel->get(array(
            'provider' => $type
        ), true);
        return $config;
    }

}