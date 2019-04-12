<?php
/**
 * User: xieibing
 * Date: 2019-3-4
 * Time: 11:18
 */

namespace ticket\api\controller\user;

use ticket\api\model\SysUserList;

class Login extends User {
    private $userModel;

    public function initialize() {
        parent::_init();
        $this->userModel = new SysUserList();
    }

    /**
     * 用户登录
     */
    public function index() {
        $param = input('post.');
        $check = $this->validate($param, 'sysUserList.login');
        if ($check !== true) {
            $this->ajaxReturn(400, $check);
        }
        $userInfo = $this->userModel->login(array('account' => $param['account']));
        if (empty($userInfo)) {
            $this->ajaxReturn(400, '用户不存在');
        }
        $password = $this->set_password($userInfo['account'], $param['password'], $userInfo['key']);
        if ($password != $userInfo['password']) {
            $this->ajaxReturn(400, '密码错误');
        }
        unset($userInfo['password']);
        unset($userInfo['key']);
        $token = $this->set_token($userInfo);
        $openid = $this->set_openid($userInfo['account']);
        cache($token, $openid);
        cache($openid, $userInfo);
        $this->userModel->where(array('account' => $param['account']))->setField('last_login_time', date('Y-m-d H:i:s', time()));
        $this->userModel->where(array('account' => $param['account']))->setInc('login_count');
        $this->ajaxReturn(0, array(
            'token' => $token,
            'openid' => $openid,
            'userInfo' => $userInfo
        ));
    }

}