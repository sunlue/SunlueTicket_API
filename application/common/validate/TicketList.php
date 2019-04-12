<?php
/**
 * User: xiebing
 * Date: 2019-3-4
 * Time: 11:25
 */

namespace ticket\common\validate;

use think\Validate;

class TicketList extends Validate {

    protected $rule = array(
        'id' => 'require|number',
        'type' => 'require',
        'name' => 'require|max:100',
        'original' => 'require',
        'present' => 'require',
        'hot' => 'in:yes,no',
        'recom' => 'in:yes,no',
        'top' => 'in:yes,no',
        'shelves' => 'in:yes,no',
    );

    protected $message = array(
        'id.require' => 'id不能为空',
        'id.number' => 'id异常',
        'type.require' => '票务类型不能为空',
        'name.require' => '显示名称不能为空',
        'name.max' => '显示名称长度异常',
        'original.require' => '票务原价不能为空',
        'present.require' => '票务售价不能为空',
        'hot.in' => '票务热门数据异常',
        'recom.in' => '票务推荐数据异常',
        'top.in' => '票务置顶数据异常',
        'shelves.in' => '票务上架数据异常',
    );

    protected $scene = array(
        'set' => ['type', 'name', 'original', 'present', 'hot', 'recom', 'top', 'shelves'],
        'edit' => ['id', 'type', 'name', 'original', 'present', 'hot', 'recom', 'top', 'shelves'],
    );
}