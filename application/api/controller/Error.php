<?php
/**
 * User: xiebing
 * Date: 2019-4-4
 * Time: 13:33
 */

namespace ticket\api\controller;
class Error {
    public function _empty() {
        return redirect('/api');
    }
}