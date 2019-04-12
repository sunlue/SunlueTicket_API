<?php
/**
 * User: xiebing
 * Date: 2019-2-25
 * Time: 15:19
 */

namespace ticket\api\controller\index;

use crypt\Base;
use Think\Db;
use Think\Image;
use ticket\common\controller\Api;

class Index extends Api {

    public function initialize() {
    }

    public function index() {
        $database = config('database.');
        try {
            new \PDO('mysql:host=' . $database['hostname'] . ';dbname=' . $database['database'], $database['username'], $database['password'], [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES'" . $database['charset'] . "';"]);
        } catch (\exception $e) {
            header("Content-Type: text/html; charset=utf-8");
            echo '<body>
			<h1>抱歉,您的系统出现错误</h1>
			    <p>您的系统出现错误，请联系管理员!</p>
			    <p>错误信息: ' . $e->getMessage() . '</p>
			<hr>
			</body>';
        }
        return 'check';

    }

    /**
     * 访问令牌
     */
    public function token() {
        parent::_init();
        $apiKey = input('server.HTTP_API_KEY');
        $arr = array_filter(array(
            'app_id' => '',
            'app_key' => $apiKey,
            'app_secret' => ''
        ));
        ksort($arr);
        $token = strtoupper(md5(Base::encrypt(urldecode(http_build_query($arr) . '&' . uniqid(time())))));
        cache(md5(strtoupper($token)), $token, array(
            'expire' => 7200
        ));
        $this->ajaxReturn(0, array(
            'access_token' => $token,
            'expires_in' => 7200
        ));
    }

    /**
     * 上传文件
     */
    public function upload() {
        parent::_init();
        parent::checkToken();
        $uploadType = input('post.uploadType');
        $file = request()->file('file');
        if ($file) {
            $config = config('upload.');
            if (empty($uploadType) || !isset($config[$uploadType])) {
                $this->ajaxReturn(400, '参数异常');
            } else {
                $config = $config[$uploadType];
            }
            if (isset($config['validate']['size']) && !empty($config['validate']['size'])) {
                $config['validate']['size'] = $config['validate']['size'] * 1024;
            }
            $info = $file->validate($config['validate'])->move($config['path'] . $config['url']);
            if ($info) {
                $url = $config['url'] . $info->getSaveName();
                $fileInfo = $info->getInfo();
                $fileInfo['url'] = str_replace('\\', '/', $url);
                //图像处理
                if (in_array($info->getMime(), ['image/png', 'image/gif', 'image/jpeg'])) {
                    //$fileInfo = $this->uploadImg($info);
                    $pathInfo = pathinfo($fileInfo['url']);
                    $fileInfo['hash'] = uniqid('_hash_');
                    Db::name('common_img')->insert(array(
                        '_hash' => $fileInfo['hash'],
                        'dir' => $pathInfo['dirname'],
                        'name' => $fileInfo['name'],
                        'ext' => $pathInfo['extension'],
                        'file' => $pathInfo['filename'],
                        'core' => (isset($fileInfo['core'])) ? 'core_' . $pathInfo['filename'] : '',
                        'thumb' => (isset($fileInfo['thumb'])) ? 'thumb_' . $pathInfo['filename'] : '',
                        'url' => $fileInfo['url'],
                        'size' => $fileInfo['size'],
                        'by_ip' => request()->ip(),
                        'by_time' => date('Y-m-d H:i:s', time())
                    ));
                } else {
                    $fileInfo['core'] = $fileInfo['thumb'] = '';
                }
                unset($fileInfo['tmp_name']);
                $fileInfo['url'] = url('/', false, false, true) . $fileInfo['url'];
                $this->ajaxReturn(0, $fileInfo);
            } else {
                $this->ajaxReturn(400, $file->getError());
            }
        }
    }

    public function uploadImg($info) {
        $fileInfo = $info->getInfo();
        $dirname = pathinfo($fileInfo['path']);
        $imgQuality = get_site_config('upload.quality');

        $maxWidth = get_site_config('upload.img_width');
        $maxHeight = get_site_config('upload.img_height');

        $imgCoreX = get_site_config('upload.img_core_x');
        $imgCoreY = get_site_config('upload.img_core_y');
        $image = Image::open($info->getPath() . DIRECTORY_SEPARATOR . $info->getFilename());
        //裁剪图片
        $coreImgPath = $info->getPath() . DIRECTORY_SEPARATOR . 'core_' . $info->getFilename();
        if ($maxWidth > 0 && $image->width() > $maxWidth) {
            $image->crop($maxWidth, $image->height(), $imgCoreX, $imgCoreY)->save($coreImgPath, null, $imgQuality);
            $fileInfo['core'] = str_replace('\\', '/', $dirname['dirname'] . DIRECTORY_SEPARATOR . 'core_' . $info->getFilename());
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
        //        Hook::listen('createImg', array(
        //            'dir' => $pathInfo['dirname'],
        //            'name' => $fileInfo['name'],
        //            'ext' => $pathInfo['extension'],
        //            'file' => $pathInfo['filename'],
        //            'core' => (isset($fileInfo['core'])) ? 'core_' . $pathInfo['filename'] : '',
        //            'thumb' => (isset($fileInfo['thumb'])) ? 'thumb_' . $pathInfo['filename'] : '',
        //            'size' => $fileInfo['size'],
        //        ));
        return $fileInfo;
    }

    public function removeUpload() {
        $hash = input('post.hash');
        if (empty($hash)) {
            $this->ajaxReturn(400, '缺少hash值');
        }
        $result = Db::name('common_img')->where(array(
            '_hash' => $hash
        ))->useSoftDelete('is_del', time())->delete();
        $this->ajaxReturn($result ? 0 : 400, $result ? 'success' : 'error');
    }
}

