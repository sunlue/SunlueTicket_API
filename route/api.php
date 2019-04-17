<?php
/**
 * User: xiebing
 * Date: 2019-3-4
 * Time: 11:17
 */

use think\facade\Route;

Route::group('api', function () {
    Route::any('access_token', 'api/index.index/token');//access_token
    Route::any('upload', 'api/index.index/upload');//上传
    Route::any('removeUpload', 'api/index.index/removeUpload');//删除上传

    Route::group('user', function () {
        Route::any('login', 'api/user.login/index');//登录
        Route::any('reg', 'api/user.reg/index');//注册
        Route::any('info', 'api/user.user/info');//用户数据
        Route::group('weixin', function () {
            Route::any('jscode2session', 'api/user.weixin/codeToSession');//小程序用户信息获取openid
            Route::any('decrypt', 'api/user.weixin/codeToSession');//小程序用户信息解密
        });
    });

    Route::group('order', function () {
        Route::any('set', 'api/order.index/set');//创建订单
        Route::any('get', 'api/order.index/get');//获取订单
        Route::any('cancel', 'api/order.index/cancel');//取消订单
        Route::any('remove', 'api/order.index/remove');//删除订单
        Route::any('detail', 'api/order.index/detail');//订单详情

        Route::group('ticket', function () {
            Route::any('get', 'api/order.ticket/get');//票务订单获取
            Route::any('member', 'api/order.ticket/member');//票务获取
            Route::any('check', 'api/order.ticket/check');//票务验票
            Route::any('refund', 'api/order.ticket/refund');//退票
            Route::any('remove', 'api/order.ticket/remove');//删除票
        });
    });

    Route::group('ticket', function () {
        Route::any('check', 'api/order.ticket/check');//票务验票
        Route::any('get', 'api/order.ticket/get');//票务获取
        Route::any('member', 'api/order.ticket/member');//会员票务
        Route::any('refund', 'api/order.ticket/refund');//退票
        Route::any('remove', 'api/order.ticket/remove');//删除票
        Route::group('setting', function () {
            Route::group('type', function () {
                Route::any('get', 'api/ticket.setting.type/get');//获取票务类型
                Route::any('set', 'api/ticket.setting.type/set');//设置票务类型
                Route::any('remove', 'api/ticket.setting.type/remove');//删除票务类型
                Route::any('enable', 'api/ticket.setting.type/enable');//启用票务类型
                Route::any('ticket', 'api/ticket.setting.type/ticket');//获取票务类型（含票务）
            });
            Route::group('know', function () {
                Route::any('all', 'api/ticket.setting.know/all');//获取票务须知
                Route::any('get', 'api/ticket.setting.know/get');//获取票务须知
                Route::any('set', 'api/ticket.setting.know/set');//设置票务须知
                Route::any('edit', 'api/ticket.setting.know/edit');//编辑票务须知
                Route::any('remove', 'api/ticket.setting.know/remove');//删除票务须知
            });
        });
        Route::group('list', function () {
            Route::any('set', 'api/ticket.lists.index/set');//添加票务列表
            Route::any('all', 'api/ticket.lists.index/all');//全部票务
            Route::any('get', 'api/ticket.lists.index/get');//获取票务
            Route::any('attr', 'api/ticket.lists.index/attr');//票务属性
            Route::any('edit', 'api/ticket.lists.index/edit');//票务编辑
            Route::any('remove', 'api/ticket.lists.index/remove');//票务删除
            Route::any('price', 'api/ticket.lists.index/getPrice');//获取票务价格(含票务信息)
            Route::any('details', 'api/ticket.lists.index/details');//票务明细
        });
        Route::group('price', function () {
            Route::any('get', 'api/ticket.price.index/get');//获取票务价格
            Route::any('set', 'api/ticket.price.index/set');//设置票务价格
        });
    });

    Route::group('pay', function () {
        Route::group('config', function () {
            Route::any('weixin', 'api/pay.config/weixin');//微信支付设置
            Route::any('alipay', 'api/pay.config/alipay');//支付宝设置
            Route::any('get', 'api/pay.config/get');//获取设置
        });
        Route::any('weixin', 'api/pay.weixin/unifiedorder');//微信统一下单
    });

    Route::group('config', function () {
        Route::any('set', 'api/config.sys/set');
        Route::any('get', 'api/config.sys/get');
    });

    Route::group('member', function () {
        Route::any('login', 'api/member.login/index');//会员登录
        Route::any('reg', 'api/member.reg/index');//会员注册
    });

    Route::group('analyze', function () {
        Route::group('order', function () {
            Route::any('bar', 'api/analyze.order/index');//订单柱状图
        });
        Route::group('access', function () {
            Route::any('visitor', 'api/analyze.access.visitor/get');
            Route::any('referer', 'api/analyze.access.referer/get');
        });
    });

}, array('method' => ['post', 'options', 'get']));