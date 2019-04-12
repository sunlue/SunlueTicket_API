<?php
/**
 * User: xiebing
 * Date: 2019-2-26
 * Time: 16:57
 */

namespace ticket\common\model;

use Think\Db;
use think\model\concern\SoftDelete;

class Common extends \think\Model {
    use SoftDelete;
    protected $noField = 'is_del,last_modify_time';
    protected $deleteTime = 'is_del';

    protected $orderState = ['待支付', '支付成功', '支付失败', '已确认', '已取消', '退款中', '已退款', '已完成', '已关闭'];
    protected $orderBodyState = ['无效', '正常', '已过期', '退款中', '已退款', '已完成'];
    protected $payType = ['weixin' => '微信支付', 'alipay' => '支付宝'];
    protected $auto = array(
        'last_modify_time'
    );

    protected function setLastModifyTimeAttr() {
        return date('Y-m-d H:i:s', time());
    }

    public function getCommonImg($where = array(), $field = '_hash,dir,name,file,ext,core,thumb,size') {
        $data = Db::name('common_img')->field($field)->where($where)->find();
        $data['url'] = ($data['url'] ? url('/', false, false, true) : '') . $data['url'];
        return $data ? $data : array();
    }

}