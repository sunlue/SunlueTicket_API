<?php
/**
 * User: xiebing
 * Date: 2019-3-4
 * Time: 11:25
 */

namespace ticket\common\validate;

use think\Validate;

class TicketPrice extends Validate {
    protected $rule = array(
        'ticket' => 'require',
        'date' => 'require|date',
        'cost' => 'require',
        'profit' => 'require',
        'number' => 'require|number|min:1',
    );

    protected $message = array(
        'ticket.require' => '票务类型不能为空',
        'date.require' => '日期不能为空',
        'date.date' => '日期异常',
        'cost.require' => '票务成本不能为空',
        'profit.require' => '票务利润不能为空',
        'number.require' => '票务库存不能为空',
        'number.number' => '票务库存数据异常',
        'number.min' => '票务库存数据异常',
    );

    protected $scene = array(
        'set' => ['ticket', 'date', 'cost', 'profit', 'number'],
        'edit' => ['ticket', 'date', 'cost', 'profit', 'number'],
    );
}