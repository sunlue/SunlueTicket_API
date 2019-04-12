<?php
/**
 * User: xieibing
 * Date: 2019-3-4
 * Time: 11:18
 */

namespace ticket\api\controller\user;

use ticket\api\model\SysUserList;

class Reg extends User {
    private $userModel;

    public function initialize() {
        parent::_init();
        $this->userModel = new SysUserList();
    }

    /**
     * 注册用户
     */
    public function index() {
        $param = input('post.');
        $check = $this->validate($param, 'sysUserList.reg');
        if ($check !== true) {
            $this->ajaxReturn(400, $check);
        }
        $checkAccount = $this->userModel->checkAccount($param['account']);
        if (!empty($checkAccount)) {
            $this->ajaxReturn(400, '账号已存在');
        }
        $param['key'] = $this->set_key();
        $param['password'] = $this->set_password($param['account'], $param['password'], $param['key']);
        parent::addAction(array(
            'model' => $this->userModel,
            'data' => $param,
            'text' => '注册用户'
        ));
    }
}