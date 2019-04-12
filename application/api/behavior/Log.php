<?php
/**
 * User: xiebing
 * Date: 2019-3-4
 * Time: 11:41
 */

namespace ticket\api\behavior;

use think\Db;

class Log {
    public function run() { }

    /**
     * sql日志记录
     * @param $result
     * @param $param
     */
    public function sql($param) {
        $array = array(
            'user_id' => '',
            'by_time' => date('Y-m-d H:i:s', time()),
            'page' => url('', false, true, true),
            'ip' => request()->ip(),
        );
        Db::name('sys_log_sql')->insert(array_merge($array, $param));
    }

    /**
     * 登录日志记录
     * @param $result
     * @param $param
     */
    public function loginLog($param) {
        $array = array(
            'by_time' => date('Y-m-d H:i:s', time()),
            'page' => url('', false, true, true),
            'by_ip' => request()->ip(),
        );
        Db::table('sys_log_login')->insert(array_merge($array, $param));
    }
}