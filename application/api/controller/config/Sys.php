<?php
/**
 * User: xiebing
 * Date: 2019-4-3
 * Time: 13:23
 */

namespace ticket\api\controller\config;

use ticket\api\model\SysConfig;
use ticket\common\controller\Api;

class Sys extends Api {
    private $sysConfigModel;

    public function initialize() {
        parent::_init();
        parent::checkToken();
        $this->sysConfigModel = new SysConfig();
    }

    public function set() {
        $param = input('post.');
        if (empty($param['provider'])) {
            $this->ajaxReturn(400, '服务商异常');
        }
        $data['provider'] = $param['provider'];
        unset($param['provider']);
        $data['config'] = $param;
        $config = $this->sysConfigModel->get(array(
            'provider' => $data['provider']
        ));
        if (empty($config)) {
            parent::addAction(array(
                'model' => $this->sysConfigModel,
                'data' => $data,
                'text' => '添加' . $data['provider'] . '配置'
            ));
        } else {
            parent::editAction(array(
                'model' => $this->sysConfigModel,
                'data' => $data,
                'text' => '修改' . $data['provider'] . '配置',
                'where' => ['provider' => $data['provider']]
            ));
        }
    }

    public function get() {
        $param = input('post.');
        if (empty($param['provider'])) {
            $this->ajaxReturn(400, '服务商异常');
        }
        $config = $this->sysConfigModel->get(array(
            'provider' => $param['provider']
        ));
        $this->ajaxReturn(0, $config);
    }

    public static function obta($type) {
        $arr = explode('.', $type);
        if ($arr) {
            $type = $arr[0];
        }
        $sysConfigModel = new sysConfig();
        $data = $sysConfigModel->get(array(
            'provider' => $type
        ), true);
        return $data ? ($arr ? $data['config'][$arr[1]] : $data['config']) : [];
    }

}