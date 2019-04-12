<?php
/**
 * User: xiebing
 * Date: 2019-3-4
 * Time: 13:18
 */

namespace ticket\api\controller\member;

use crypt\Base;
use ticket\common\controller\Api;

class Member extends Api {

    /**
     * 生成登录密码
     * @param $account
     * @param $password
     * @param $key
     * @return string
     */
    public function set_password($account, $password, $key) {
        $arr = array_filter(array($account, $password, $key));
        ksort($arr);
        return strtoupper(md5(urldecode(http_build_query($arr))));
    }

    /**
     * 生成key
     * @return string
     */
    public function set_key() {
        return strtoupper(Base::encrypt(uniqid()));
    }

    /**
     * 生成token
     */
    public function set_token($arr = array()) {
        $arr = array_filter($arr);
        ksort($arr);
        $token = strtoupper(md5(Base::encrypt(urldecode(http_build_query($arr) . '&' . uniqid()))));
        cache(md5(strtoupper($token)), $token);
        return $token;
    }

    /**
     * 生成openid
     * @param string $string
     * @return string
     */
    public function set_openid($string = '') {
        return strtoupper(substr(md5($string ? $string : uniqid()), 8, 16));
    }
}