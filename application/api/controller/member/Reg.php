<?php
/**
 * User: xiebing
 * Date: 2019-4-11
 * Time: 9:16
 */

namespace ticket\api\controller\member;

use ticket\api\model\MemberWeb;

class Reg extends Member {
    protected static $loginType = array(
        'h5'
    );

    private $memberWebModel;

    protected function initialize() {
        parent::_init();
        parent::checkToken();
        $this->memberWebModel=new MemberWeb();
    }

    public function index() {
        $param = input('post.');
        if (empty($param['login_type']) || !in_array($param['login_type'], self::$loginType)) {
            $this->ajaxReturn(400, '登录态错误');
        }
        switch ($param['login_type']) {
            case 'h5':
                $check = $this->validate($param, 'MemberWeb.reg');
                if ($check !== true) {
                    $this->ajaxReturn(400, $check);
                }
                break;
            case 'h5':
                $check = $this->validate($param, 'MemberWeb.reg');
                if ($check !== true) {
                    $this->ajaxReturn(400, $check);
                }
                break;
        }
        $loginType = $param['login_type'];
        $this->$loginType($param);
    }

    public function h5($param){
        $checkAccount = $this->memberWebModel->checkAccount($param['account']);
        if (!empty($checkAccount)) {
            $this->ajaxReturn(400, '账号已存在');
        }
        $checkMobile = $this->memberWebModel->checkAccount(array(
            'mobile'=>$param['mobile']
        ));
        if (!empty($checkMobile)) {
            $this->ajaxReturn(400, '手机号已存在');
        }
        $param['key'] = $this->set_key();
        $param['password'] = $this->set_password($param['account'], $param['password'], $param['key']);
        parent::addAction(array(
            'model' => $this->memberWebModel,
            'data' => $param,
            'text' => '注册用户'
        ),function ($result) use ($param){
            if ($result[0]['code']!=0){
                $this->ajaxReturn($result[0],$result[1]);
            }
            $where[]=['account','=',$param['account']];
            $userInfo=$this->memberWebModel->login($where);
            unset($userInfo['password']);
            unset($userInfo['key']);
            $token = $this->set_token($userInfo);
            $openid = $this->set_openid($userInfo['account']);
            cache($token, $openid);
            cache($openid, $userInfo);
            $this->memberWebModel->where($where)->setField('last_login_time',date('Y-m-d H:i:s',time()));
            $this->memberWebModel->where($where)->setInc('login_count');
            $this->ajaxReturn(0, array(
                'token' => $token,
                'openid' => $openid,
                'userInfo' => $userInfo
            ));
        });
    }
}

