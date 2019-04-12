<?php
/**
 * User: xiebing
 * Date: 2019-3-4
 * Time: 11:25
 */

namespace ticket\common\validate;

use think\Validate;

class TicketKnow extends Validate {

    protected $rule = array(
        'uniqid' => 'require',
        'book_type' => 'require|number',
        'book_day' => 'requireCallback:requireBookType|number',
        'aging_type' => 'require|number',
        'aging_day' => 'requireCallback:requireAgingType|number',
    );

    protected $message = array(
        'uniqid.require' => 'id不能为空',
        'book_type.require' => '票务预订时间类型不能为空',
        'book_type.number' => '票务预订时间类型异常',
        'book_day.requireBookType' => '票务预订时间天数不能为空',
        'book_day.number' => '票务预订时间天数异常',
        'aging_type.require' => '票务使用时间类型不能为空',
        'aging_type.number' => '票务使用时间类型异常',
        'aging_day.number' => '票务使用时间天数异常',
        'book_day.requireAgingType' => '票务使用时间天数不能为空',
    );

    protected $scene = array(
        'set' => ['book_type', 'book_day', 'aging_type'],
        'edit' => ['uniqid', 'book_type', 'book_day', 'aging_type'],
        'remove' => ['uniqid'],
    );

    protected function requireBookType($value, $data) {
        if ($data['book_type'] != '1' && $value < 0) {
            return true;
        }
    }

    protected function requireAginfType($value, $data) {
        if ($data['book_type'] != '1' && $value < 0) {
            return true;
        }
    }
}