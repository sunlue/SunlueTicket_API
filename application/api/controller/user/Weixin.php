<?php
/**
 * User: xiebing
 * Date: 2019-4-3
 * Time: 10:14
 */
namespace ticket\api\controller\user;
class Weixin extends User{

    public function initialize() {
        parent::_init();
    }

    public function codeToSession(){
        $param=input('post.');
        $check=$this->validate($param,array(
            'appid'=>'require',
            'secret'=>'require',
            'js_code'=>'require',
            'iv'=>'require',
            'encryptedData'=>'require',
        ),array(
            'appid.require'=>'appid必须',
            'secret.require'=>'secret必须',
            'js_code.require'=>'js_code必须',
            'iv.require'=>'iv必须',
            'encryptedData.require'=>'encryptedData必须',
        ));
        if ($check!==true){
            $this->ajaxReturn(400,$check);
        }
        $config = array(
            'mini_program'=>array(
                'app_id' => $param['appid'],
                'secret' => $param['secret'],
                'token'=>'',
                'aes_key'=>''
            )
        );
        $weixin = new \EasyWeChat\Foundation\Application($config);
        $sessionCode=$weixin->mini_program->sns->getSessionKey($param['js_code']);
        $sessionCode=$sessionCode->toArray();
        $userInfo=$weixin->mini_program->encryptor->decryptData($sessionCode['session_key'], $param['iv'], $param['encryptedData']);
        $this->ajaxReturn(0,$userInfo);
    }

}