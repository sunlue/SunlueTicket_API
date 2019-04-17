<?php
/**
 * User: Administrator
 * Date: 2019-2-22
 * Time: 18:24
 */

namespace ticket\common\controller;

use think\Controller;
use think\facade\Hook;

class Api extends Controller {

    public function _init() {
        //header('Access-Control-Allow-Origin:*');
        //header('Access-Control-Allow-Headers:content-type,AUTHORIZATION,AUTH_TOKEN,api-key');
        if (request()->isOptions()) {
            $this->ajaxReturn(0);
        }
        $Authorization = input('server.HTTP_AUTHORIZATION');
        $api_key = input('server.HTTP_API_KEY');
        if (isset($Authorization)) {
            if (isset($_SERVER['HTTP_REFERER'])) {
                $fromUrl = parse_url($_SERVER['HTTP_REFERER']);
                $Authorization = input('server.HTTP_AUTHORIZATION');
                if ($fromUrl['host'] != $Authorization) {
                    $this->ajaxReturn(400, '非法操作，未被授权');
                }
            } else {
                $this->ajaxReturn(400, '非法请求');
            }
        } elseif (empty($api_key)) {
            $this->checkToken();
        }
    }

    public function checkToken() {
        $token = input('server.HTTP_AUTH_TOKEN');
        if (empty($token)) {
            $this->ajaxReturn(400, 'client 令牌授权过期或异常');
        }
        $origToken = cache(md5(strtoupper($token)));
        if (empty($origToken)) {
            $this->ajaxReturn(400, 'server 令牌授权过期或异常');
        }
        if (empty($origToken) || $token != $origToken) {
            $this->ajaxReturn(400, '令牌授权过期或错误');
        }
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
                $return ['info'] = 'SUCCESS';
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
     * 上传文件
     */
    public function upload() {
        $param = input('post.');
        $file = request()->file('file');
        if ($file) {
            $config = config('upload');
            if (empty($param['type']) || !isset($config[$param['type']])) {
                $this->ajaxReturn(400, '配置错误');
            }
            $config = $config[$param['type']];
            if (isset($config['validate']['size']) && !empty($config['validate']['size'])) {
                $config['validate']['size'] = $config['validate']['size'] * 1024;
            }
            $info = $file->validate($config['validate'])->move(INDEX_PATH . $config['path']);
            if ($info) {

                $fileInfo = $info->getInfo();
                $fileInfo['path'] = str_replace('\\', '/', $config['path'] . DS . $info->getSaveName());
                $dirname = pathinfo($fileInfo['path']);
                //图像处理
                if (in_array($info->getMime(), config('filemime.img'))) {
                    $imgQuality = get_site_config('upload.quality');

                    $maxWidth = get_site_config('upload.img_width');
                    $maxHeight = get_site_config('upload.img_height');

                    $imgCoreX = get_site_config('upload.img_core_x');
                    $imgCoreY = get_site_config('upload.img_core_y');
                    $image = Image::open($info->getPath() . DS . $info->getFilename());

                    //裁剪图片
                    $coreImgPath = $info->getPath() . DS . 'core_' . $info->getFilename();
                    if ($maxWidth > 0 && $image->width() > $maxWidth) {
                        $image->crop($maxWidth, $image->height(), $imgCoreX, $imgCoreY)->save($coreImgPath, null, $imgQuality);
                        $fileInfo['core'] = str_replace('\\', '/', $dirname['dirname'] . DS . 'core_' . $info->getFilename());
                    }
                    if ($maxHeight > 0 && $image->height() > $maxHeight) {
                        if (file_exists($coreImgPath)) {
                            $coreWidthImg = Image::open($coreImgPath);
                            $coreWidthImg->crop($coreWidthImg->width(), $maxHeight, $imgCoreX, $imgCoreY)->save($coreImgPath, null, $imgQuality);
                        } else {
                            $image->crop($image->width(), $maxHeight, $imgCoreX, $imgCoreY)->save($coreImgPath, null, $imgQuality);
                        }
                        $fileInfo['core'] = str_replace('\\', '/', $dirname['dirname'] . DS . 'core_' . $info->getFilename());
                    }
                    //生成缩略图
                    $thumbWidth = get_site_config('upload.thumbnail_width');
                    $thumbHeight = get_site_config('upload.thumbnail_height');
                    $thumbImgPath = $info->getPath() . DS . 'thumb_' . $info->getFilename();
                    if ($thumbWidth > 0 && $thumbHeight > 0) {
                        $thumbType = get_site_config('upload.thumbnail_type');
                        $image->thumb($thumbWidth, $thumbHeight, (int)$thumbType)->save($thumbImgPath, null, $imgQuality);
                        $fileInfo['thumb'] = str_replace('\\', '/', $dirname['dirname'] . DS . 'thumb_' . $info->getFilename());
                    }

                    //记录到图片空间
                    $pathInfo = pathinfo($fileInfo['path']);
                    Hook::listen('createImg', array(
                        'dir' => $pathInfo['dirname'],
                        'name' => $fileInfo['name'],
                        'ext' => $pathInfo['extension'],
                        'file' => $pathInfo['filename'],
                        'core' => (isset($fileInfo['core'])) ? 'core_' . $pathInfo['filename'] : '',
                        'thumb' => (isset($fileInfo['thumb'])) ? 'thumb_' . $pathInfo['filename'] : '',
                        'size' => $fileInfo['size'],
                    ));

                } else {
                    $fileInfo['core'] = $fileInfo['thumb'] = '';
                }
                $this->ajaxReturn(0, $fileInfo);
            } else {
                $this->ajaxReturn(400, $file->getError());
            }
        }
    }

    protected function fieldFilter($model) {
        $data = $model->getData();
        unset($data['is_del']);
        unset($data['last_modify_time']);
        return $data;
    }

    public function addAction($param, $callback = '') {
        debug('begin');
        $model = $param['model'];
        try {
            $res = $model->allowField(true)->save($param['data']);
            $result = array($res ? 0 : 400, $res ? $this->fieldFilter($model) : '操作失败');
        } catch (\exception $e) {
            $result = array(500, '发生异常,操作失败');
            $abort = $e->getMessage();
        }
        debug('end');
        Hook::listen('sql', array(
            'state' => $result[0] == 500 ? 2 : 1,
            'sql' => $model->getLastSql(),
            'abort' => empty($abort) ? '' : $abort,
            'text' => $param['text'],
            'type' => 'create',
            'time' => debug('begin', 'end', 6) . '秒',
        ));
        if ($callback instanceof \Closure) {
            $callback(array($result[0], $result[1]));
        } else {
            $this->ajaxReturn($result[0], $result[1]);
        }
    }

    public function editAction($param, $callback = '') {
        debug('begin');
        $model = $param['model'];
        try {
            $res = $model->allowField(true)->save($param['data'], $param['where']);
            $result = array($res ? 0 : 400, $res ? $this->fieldFilter($model) : '修改失败');
        } catch (\exception $e) {
            $result = array(500, '发生异常,修改失败');
            $abort = $e->getMessage();
        }
        debug('end');
        Hook::listen('sql', array(
            'state' => $result[0] == 500 ? 2 : 1,
            'sql' => $model->getLastSql(),
            'abort' => empty($abort) ? '' : $abort,
            'text' => $param['text'],
            'type' => 'update',
            'time' => debug('begin', 'end', 6) . '秒',
        ));
        if ($callback instanceof \Closure) {
            $callback(array($result[0], $result[1]), function () use ($result) {
                $this->ajaxReturn($result[0], $result[1]);
            });
        } else {
            $this->ajaxReturn($result[0], $result[1]);
        }
    }

    public function delAction($param, $callback = '') {
        debug('begin');
        $model = $param['model'];
        try {
            $res = $model->destroy($param['where']);
            $result = array($res ? 0 : 400, $res ? '删除成功' : '删除失败');
        } catch (\exception $e) {
            $result = array(500, '发生异常,删除失败');
            $abort = $e->getMessage();
        }
        debug('end');
        Hook::listen('sql', array(
            'state' => $result[0] == 500 ? 2 : 1,
            'sql' => $model->getLastSql(),
            'abort' => empty($abort) ? '' : $abort,
            'text' => $param['text'],
            'type' => 'remove',
            'time' => debug('begin', 'end', 6) . '秒',
        ));
        if ($callback instanceof \Closure) {
            $callback(array($result[0], $result[1]));
        } else {
            $this->ajaxReturn($result[0], $result[1]);
        }
    }

}