<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/3
 * Time: 3:00
 */

namespace app\common\lib;

use app\common\lib\Aes;
use think\Cache;

class IAuth {

    /**
     * 密码加密
     * @param $pwd
     * @return string
     */
    public static function setPwd($pwd) {
        return md5($pwd.config('app.password_halt'));
    }

    /**
     * 生成校验 Sign
     */
    public static function setSign($data = []) {
        // 1 数组键名升序排列
        ksort($data);

        // 2 拼接数组值为字符串
        $str = http_build_query($data);

        // 3 AES 加密
        $aesStr = (new Aes())->encrypt($str);

        // 4 转为大写
        // $aesStr = strtoupper($aesStr);
        return $aesStr;
    }

    /** 
     * 检查检验 sign
     * @param array $data
     * @return bool
    */
    public static function checkSignPass( $data) {
        $str = (new Aes())->decrypt($data['sign']);
        if (empty($str)) {
            return false;
        }
        parse_str($str, $arr);
        // halt($arr);

        // 基础数据校验
        if (!is_array($arr) || empty($arr['version']) || $arr['version'] != $data['version']) {
            return false;
        }

        if (!config('app_debug')) {
            // 是否过期
            if (empty($arr['time']) || (time() - ceil($arr['time'])/1000) > config('app.app_sign_time')) {
                return false;
            }

            // 唯一性判定：是否使用
            // halt(Cache::get($data['sign']));
            if (Cache::get($data['sign'])) {
                return false;
            }
        }
        return true;
    }
}