<?php
/**
 * User: xiebing
 * Date: 2019-4-3
 * Time: 15:05
 */

namespace ticket\api\controller\member;

use ticket\api\model\MemberWeb;
use ticket\oauth\model\MemberWeixin;

class Login extends Member {
    protected static $loginType = array(
        'mp_weixin','h5'
    );
    private $memberWeixinModel;
    private $memberWebModel;

    public function initialize() {
        parent::_init();
        $this->memberWeixinModel = new MemberWeixin();
        $this->memberWebModel = new MemberWeb();
    }

    /**
     * 用户登录
     */
    public function index() {
        $param = input('post.');
        if (empty($param['login_type']) || !in_array($param['login_type'], self::$loginType)) {
            $this->ajaxReturn(400, '登录态错误');
        }
        $where = [];
        switch ($param['login_type']) {
            case 'mp_weixin';
                if (empty($param['token']) && empty($param['uniqid'])) {
                    $this->ajaxReturn(400, '授权信息错误');
                }
                if (!empty($param['token'])) {
                    $where[] = ['token', '=', $param['token']];
                }
                if (!empty($param['uniqid'])) {
                    $where[] = ['uniqid', '=', $param['uniqid']];
                }
                break;
            case 'h5':
                $check = $this->validate($param, 'MemberWeb.login');
                if ($check !== true) {
                    $this->ajaxReturn(400, $check);
                }
                $where[]=['account','=',$param['account']];
                break;
        }
        $loginType = $param['login_type'];
        $this->$loginType($where);
    }

    public function mp_weixin($where) {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') == false) {
            $this->ajaxReturn(400, '请在微信浏览器打开');
        }
        $userInfo = $this->memberWeixinModel->login($where);
        if (empty($userInfo)){
            $this->ajaxReturn(400, '信息读取异常');
        }
        $token = $this->set_token($userInfo);
        $openid = $this->set_openid($userInfo['openid']);
        cache($token, $openid);
        cache($openid, $userInfo);
        $this->ajaxReturn(0, array(
            'token' => $token,
            'openid' => $openid,
            'userInfo' => $userInfo
        ));
    }

    public function h5($where){
        $param = input('post.');
        $userInfo = $this->memberWebModel->login($where);
        if (empty($userInfo)) {
            $this->ajaxReturn(400, '用户不存在');
        }
        $password = $this->set_password($userInfo['account'], $param['password'], $userInfo['key']);
        if ($password != $userInfo['password']) {
            $this->ajaxReturn(400, '密码错误');
        }
        unset($userInfo['password']);
        unset($userInfo['key']);
        $token = $this->set_token($userInfo);
        $openid = $this->set_openid($userInfo['account']);
        cache($token, $openid);
        cache($openid, $userInfo);
        $this->memberWebModel->where($where)->setField('last_login_time',date('Y-m-d H:i:s',time()));
        $this->memberWebModel->where($where)->setInc('login_count');
        $this->ajaxReturn(0, array(
            'token' => $token,
            'openid' => $openid,
            'userInfo' => $userInfo
        ));
    }

}