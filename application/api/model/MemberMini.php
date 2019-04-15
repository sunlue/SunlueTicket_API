<?php
/**
 * User: xiebing
 * Date: 2018/10/8
 * Time: 16:06
 */

namespace ticket\api\model;

use ticket\common\model\Common;

class MemberMini extends Common {

    protected $pk = 'uniqid';

    protected $insert = array(
        'uniqid',
        'add_time',
        'provider' => 'weixin'
    );

    protected function setUniqidAttr() {
        return uniqid('mimi_weixin_');
    }

    protected function setAddTimeAttr() {
        return date('Y-m-d H:i:s', time());
    }

    /**
     * 检测账号是否存在
     * @param $account 账号
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function checkAccount($account) {
        if (is_array($account)) {
            $where = $account;
        } else {
            $where = array('account' => $account);
        }
        return MemberMini::alias('a')->where($where)->find();
    }


    /**
     * 登录
     * @param array $where 查询条件
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login($where = array()) {
        $data = MemberMini::alias('a')->field('last_modify_time,is_del', true)->where($where)->find();
        return $data ? $data->toArray() : array();
    }

    /**
     * 获取用户数据全部
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAll($where = array()) {
        $data = MemberMini::alias('a')->field('password,key,last_modify_time,is_del', true)->where($where)->select();
        return $data ? self::selectToArray($data) : array();
    }

    /**
     * 获取用户数据列表
     * @param array $where 查询条件
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($where = array()) {
        $data = MemberMini::alias('a')->field('password,key,last_modify_time,is_del', true)
            ->page(input('post.page', 1))->limit(input('post.limit', 10))->where($where)->order('id desc')->select();
        $count = MemberWeb::alias('a')->where($where)->count();
        return array(
            'list' => $data ? self::selectToArray($data) : array(),
            'count' => $count
        );
    }

    /**
     * 获取用户数据
     * @param array $where
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getFind($where=array()){
        return MemberMini::where($where)->field($this->noField,true)->find()->toArray();
    }
}