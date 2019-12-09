<?php

/**
 * Created by PhpStorm.
 * User: 24200
 * Date: 2018/3/18
 * Time: 12:54
 */
return [
    'default_return_type' => 'json',
    /*
    // local
    'wafer_config' => [
        'appid' => 'aaaaaaaaaaa',
        'appsecret' => 'ccccccccccccccc',
        'wxurl' => 'https://api.weixin.qq.com/sns/jscode2session?',
        'grant_type' => 'authorization_code',
    ],*/
    // online
    'wafer_config' => [],
    //分页配置
    'paginate' => [
        'type'      => 'bootstrap',
        'var_page'  => 'page',
        'list_rows' => 10,
    ],
];
