<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-1-17
 * Time: 10:38
 */

namespace ticket\index\controller\total;

use ticket\common\controller\Sunlue;

class Access extends Sunlue {
    private $accessModel;
    public function initialize() {
        $this->accessModel = new \ticket\index\model\Access();
    }

    public function index() {
        $cookie = cookie('SunlueTiceketACCESS');
        if (!$cookie) {
            $cookie = cookie('SunlueTiceketACCESS', time() . rand(1000, 9999));
        }
        if (isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
            if (!empty($referer)) {
                $domain = parse_url($referer);
                $host = $domain['host'];
            }
        } else {
            $referer = '';
        }
        $array = array(
            'type' => 'pc',
            'ip' => $this->getAccessIp(),
            'referer' => !empty($referer) ? $referer : '',
            'domain' => !empty($referer) ? $host : '',
            'date' => date('Y-m-d', time()),
            'time' => date('H:i:s', time()),
            'cookie' => $cookie
        );
        $this->accessModel->save($array);
    }

    protected function getAccessIp($ip = false) {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) {
                array_unshift($ips, $ip);
                $ip = FALSE;
            }
            for ($i = 0; $i < count($ips); $i++) {
                if (!eregi("^(10│172.16│192.168).", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }
}