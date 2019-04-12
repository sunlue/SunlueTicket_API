<?php
/**
 * User: xiebing
 * Date: 2019-3-4
 * Time: 11:25
 */

namespace ticket\common\validate;

use think\Validate;

class OrderBody extends Validate {
    protected $rule = array(
        'id' => 'require|number',
        'ticket_id' => 'require|number|checkTicket',
        'number' => 'require|number',
        'date' => 'require|dateFormat:Y-m-d',
    );

    protected $message = array(
        'ticket_id.require' => '票务异常',
        'ticket_id.number' => '票务异常',
        'ticket_id.checkTicket' => '票务不存在',
        'number.require' => '数量异常',
        'number.number' => '数量异常',
        'date.require' => '到达时间异常',
        'date.dateFormat' => '到达时间格式异常',
    );

    protected $scene = array(
        'set' => ['ticket_id', 'number'],
    );

    protected function checkTicket($value) {
        return \ticket\api\model\TicketList::find($value) ? true : false;
    }

}