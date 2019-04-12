<?php
/**
 * User: xiebing
 * Date: 2019-3-4
 * Time: 11:25
 */

namespace ticket\common\validate;

use think\Validate;

class SysUserList extends Validate {
    protected $rule = array(
        'id' => 'require|number',
        'account' => 'require',
        'password' => 'require|length:6,24',
        'password1' => 'confirm:password',
    );

    protected $message = array(
        'account.require' => '账号不能为空',
        'password.require' => '密码不能为空',
        'password.length' => '密码长度不正确',
    );

    protected $scene = array(
        'reg' => ['account', 'password'],
        'login' => ['account', 'password'],
        'edit' => ['account'],
    );
}