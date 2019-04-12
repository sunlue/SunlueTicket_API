<?php
/**
 * User: xiebing
 * Date: 2019-3-4
 * Time: 11:25
 */

namespace ticket\common\validate;

use think\Validate;

class PayConfig extends Validate {
    protected $rule = array(
        'id' => 'require|number',
        'provider' => 'require',
        'swtich' => 'require|in:open,close',
        'config' => 'requireIf:swtich,open',
    );

    protected $message = array(
        'provider.require' => '服务商不能为空',
        'swtich.require' => '状态不能为空',
        'swtich.in' => '状态错误',
        'config.requireIf' => '配置必须',
    );

    protected $scene = array(
        'add' => ['provider', 'swtich','config'],
        'edit' => ['swtich','config'],
        'get' => ['provider'],
    );
}