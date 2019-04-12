<?php
/**
 * User: xiebing
 * Date: 2019-4-3
 * Time: 11:37
 */

namespace ticket\oauth\controller;

use ticket\api\controller\config\Sys;
use ticket\common\controller\Sunlue;
use ticket\oauth\model\MemberWeixin;

class Weixin extends Sunlue {
    private $weixin;

    public function initialize() {
        $config = Sys::obta('weixin');
        $options = array(
            'app_id' => $config['public']['appid'],
            'secret' => $config['public']['secret'],
            'oauth' => [
                'scopes' => [$config['public']['scope']],
                'callback' => url('/oauth/weixin/callback', false, false, true) . '?client=' . urlencode(input('callback')),
            ],
        );
        $this->weixin = new \EasyWeChat\Foundation\Application($options);
    }

    public function index() {
        $this->weixin->oauth->redirect()->send();
    }

    public function callback() {
        $memberWeixin = new MemberWeixin();
        $user = $this->weixin->oauth->user();
        $userInfo = $user->getOriginal();
        $userInfo['token'] = $user->getToken();
        unset($userInfo['privilege']);
        $result = $memberWeixin->where(array(
            'openid' => $user->getId()
        ))->update($userInfo);
        if ($result < 1) {
            $memberWeixin->isUpdate(false)->save($userInfo);
        }
        $client = input('client');
        $clientUrl = parse_url($client);
        $suffix = isset($clientUrl['query']) ? '&' : '?';
        header('Location:' . $client . $suffix . 'token=' . $userInfo['token']);
    }
}















