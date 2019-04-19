<?php
/**
 * User: xiebing
 * Date: 2019-4-19
 * Time: 11:37
 */

namespace ticket\index\model;

use Think\Db;
use ticket\common\model\Common;

class Access extends Common {
    protected $pk = 'id';

    public function getPv($where = array(), $isSql = false) {
        $data = Access::alias('a')->field('count(*) as total,date')->where($where)->group('date')->fetchSql($isSql)->select();
        return !$isSql ? $data->toArray() : $data;
    }

    public function getUv($where = array(), $isSql = false) {
        $sql = Access::alias('a')->field('count(*),date,ip')->group('date,cookie')->fetchSql()->select();
        $data = Db::table("($sql) as t")->field('COUNT(*) as total,`date`')->where($where)->group('date')->fetchSql($isSql)->select();
        return !$isSql ? $data->toArray() : $data;
    }

    public function getIp($where = array(), $isSql = false) {
        $sql = Access::alias('a')->field('count(*),date,ip')->group('date,ip')->fetchSql(true)->select();
        $data = Db::table("($sql) as t")->field('COUNT(*) as total,`date`')->where($where)->fetchSql($isSql)->group('date')->select();
        return !$isSql ? $data->toArray() : $data;
    }
}