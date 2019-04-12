<?php
/**
 * User: xiebing
 * Date: 2019-3-6
 * Time: 13:54
 */
return array(
    'img' => array(
        'path' => env('root_path') . 'public' . DIRECTORY_SEPARATOR,
        'url' => 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR,
        'validate' => [
            'ext' => 'gif,jpg,png,bmp,jpeg',
            'size' => 10240
        ]
    )
);