<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/3
 * Time: 2:15
 */
namespace app\common\validate;

use think\Validate;

class Login extends Validate {
    protected $rule = ['username'=>'require|max:20','password'=>'require|max:20'];
    protected $message = ['username.require'=>'用户名不能为空','username.max'=>'用户名最长不能超过20个字符','password.require'=>'密码不能为空','password.max'=>'密码最长不能超过20个字符',];
}