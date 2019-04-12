<?php
/**
 * User: xiebing
 * Date: 2019-4-3
 * Time: 11:39
 */

use think\facade\Route;

Route::group('oauth', function () {
    Route::group('weixin', function () {
        Route::any('init', 'oauth/weixin/index');
        Route::any('callback', 'oauth/weixin/callback');
    });
});