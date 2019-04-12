<?php
/**
 * User: xiebing
 * Date: 2019-3-4
 * Time: 11:25
 */

namespace ticket\common\validate;

use think\Validate;

class OrderList extends Validate {
    protected $rule = array(
        'order_sn' => 'require|unique:order_list|checkOrderSn',
        'date' => 'require|dateFormat:Y-m-d',
        'contact' => 'require',
        'mobile' => 'require|mobile',
        'note' => 'max:255'
    );

    protected $message = array(
        'order_sn.require' => '订单号异常',
        'order_sn.unique' => '订单号重复',
        'order_sn.checkOrderSn' => '订单号异常',
        'date.require' => '到达时间异常',
        'date.dateFormat' => '到达时间格式异常',
        'contact.require' => '联系人异常',
        'mobile.require' => '手机号异常',
        'mobile.mobile' => '手机号异常',
        'note.max' => '备注异常',
    );

    protected $scene = array(
        'set' => ['order_sn', 'date', 'contact', 'mobile', 'note'],
        'cancel' => ['order_sn'],
    );

    protected function sceneSet() {
        return $this->remove('order_sn', 'checkOrderSn');
    }

    protected function sceneCancel() {
        return $this->only(['order_sn'])->remove('order_sn', 'unique');
    }

    protected function checkOrderSn($value) {
        $trade_no = strtoupper(md5($value));
        $where['order_sn'] = $value;
        return \ticket\api\model\OrderList::where($where)->find() ? true : false;
    }

}