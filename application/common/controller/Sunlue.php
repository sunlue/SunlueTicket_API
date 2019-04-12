<?php
/**
 * User: Administrator
 * Date: 2019-2-22
 * Time: 18:24
 */

namespace ticket\common\controller;

use think\Controller;

class Sunlue extends Controller {
    protected function checkLogin() {
    }

    /**
     * 渲染视图
     * @param bool $static 渲染资源
     * @return \think\response\View
     */
    protected function view($static = true) {
        if ($static === true || gettype($static) == 'string') {
            $c = strtolower(str_replace('.', '/', request()->controller()));
            $m = strtolower(request()->module());
            $resources = config('template.tpl_replace_string.__STATIC__') . '/' . $m;
            $this->assign('res', $resources);
            $this->assign('static', array(
                'css' => $resources . '/css/' . $c . '/',
                'js' => $resources . '/js/',
                'json' => $resources . '/json/' . $c . '/',
                'img' => $resources . '/img/' . $c . '/',
            ));
        }
        $page = (gettype($static) == 'string' ? $static : request()->action());
        return view($page);
    }

    /**
     * ajax返回
     * @param int $code
     * @param string $info
     * @param array $data
     * @param string $type
     */
    protected function ajaxReturn($code = 0, $info = '', $data = [], $type = '') {
        if (empty ($type)) {
            $type = config('DEFAULT_AJAX_RETURN');
        }
        if (is_array($code)) {
            $return = $code;
        } else {
            $return ['code'] = $code;
            if ($code == 0 && is_array($info)) {
                $return ['data'] = $info;
                $return ['info'] = '请求成功';
            } elseif ($code != 0 && !empty ($data)) {
                $return ['data'] = $data;
            }
            if ($code == 0 && !is_array($info) && !empty ($info)) {
                $return ['info'] = $info;
            } elseif ($code == 0 && empty ($info)) {
                $return ['info'] = '请求成功';
            } elseif ($code != 0 && !empty ($info)) {
                $return ['info'] = $info;
            }
        }
        switch (strtoupper($type)) {
            case 'XML' :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                $result = (arrtoxml($return));
            case 'JSONP' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler = isset ($_GET [config('VAR_JSONP_HANDLER')]) ? $_GET [config('VAR_JSONP_HANDLER')] : config('DEFAULT_JSONP_HANDLER');
                exit ($handler . '(' . json_encode($return, JSON_UNESCAPED_UNICODE) . ');');
            case 'HTML' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                $result = (arrtoul($return));
            case 'TEXT' :
                // 返回txt格式数据
                header('Content-Type:text/plain; charset=utf-8');
                $result = (var_export($return));
            default :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $result = json_encode($return, JSON_UNESCAPED_UNICODE);
        }
        exit ($result);
    }

    /**
     * 返回table数据格式
     * @param int $code
     * @param array $data
     * @param int $count
     * @param string $msg
     */
    protected function layuiTable($code = 0, $data = [], $count = 0, $msg = '') {
        if (isset($data['count']) && is_numeric($data['count'])) {
            $count = $data['count'];
        }
        if (isset($data['list']) && is_array($data['list'])) {
            $data = $data['list'];
        }
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode(array(
            'code' => $code,
            'data' => $data,
            'count' => $count,
            'msg' => $msg
        ), JSON_UNESCAPED_UNICODE));
    }

}