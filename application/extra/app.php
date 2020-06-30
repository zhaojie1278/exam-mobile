<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/3
 * Time: 3:01
 */

return [
    'password_halt' => '_$excel_pwd', // 密码加密盐
    'aeskey' => '123123', // aes 密钥，服务的和客户端一致
    'apptypes' => ['IOS','WAFER'], // APP 类型
    'app_sign_time' => 100000, // SIGN 有效期
    'app_sign_cache_time' => 200000, // SIGN 缓存失效时间
    /*'email_account' => '###@126.com', // 发送邮件的邮箱
    'email_host' => 'smtp.126.com', // 邮箱host
    'email_authcode' => '###', // 邮箱授权码
    'email_fromer' => '考试系统', // 邮箱发送人*/
];