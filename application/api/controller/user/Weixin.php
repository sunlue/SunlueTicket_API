<?php
/**
 * User: xiebing
 * Date: 2019-4-3
 * Time: 10:14
 */

namespace ticket\api\controller\user;

use ticket\api\controller\config\Sys;
use ticket\api\model\MemberMini;

class Weixin extends User {

    private $memberMiniModel;

    public function initialize() {
        parent::_init();
        $this->memberMiniModel = new MemberMini();
    }

    public function codeToSession() {
        $config = Sys::obta('weixin.mini_program');
        if ($config['swtich'] != 'open') {
            $this->ajaxReturn(400, '未开启微信小程序');
        }
        $param = input('post.');
        $param['secret'] = $config['secret'];
        $check = $this->validate($param, array(
            'appid' => 'require',
            'secret' => 'require',
            'js_code' => 'require',
            'iv' => 'require',
            'encryptedData' => 'require',
        ), array(
            'appid.require' => 'appid必须',
            'secret.require' => 'secret必须',
            'js_code.require' => 'js_code必须',
            'iv.require' => 'iv必须',
            'encryptedData.require' => 'encryptedData必须',
        ));
        if ($check !== true) {
            $this->ajaxReturn(400, $check);
        }
        if ($config['appid'] != $param['appid']) {
            $this->ajaxReturn(400, '微信小程序配置不一致');
        }
        $config = array(
            'mini_program' => array(
                'app_id' => $param['appid'],
                'secret' => $param['secret'],
                'token' => '',
                'aes_key' => ''
            )
        );
        $weixin = new \EasyWeChat\Foundation\Application($config);
        $sessionCode = $weixin->mini_program->sns->getSessionKey($param['js_code']);
        $sessionCode = $sessionCode->toArray();
        $userInfo = $weixin->mini_program->encryptor->decryptData($sessionCode['session_key'], $param['iv'], $param['encryptedData']);
        $userInfo['appid'] = $userInfo['watermark']['appid'];
        unset($userInfo['watermark']);
        $userInfo['last_modify_time'] = date('Y-m-d H:i:s', time());
        $result = $this->memberMiniModel->where(array(
            'openId' => $userInfo['openId']
        ))->update($userInfo);
        if ($result < 1) {
            $this->memberMiniModel->isUpdate(false)->allowField(true)->save($userInfo);
            $userInfo = $this->memberMiniModel->getData();
        } else {
            $userInfo = $this->memberMiniModel->getFind(array(
                'openId' => $userInfo['openId']
            ));
        }
        $this->ajaxReturn(0, $userInfo);
    }

    public function decrypt() {

    }

}