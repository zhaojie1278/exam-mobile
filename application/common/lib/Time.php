<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/3
 * Time: 3:00
 */

namespace app\common\lib;

class Time {

    /**
     * 获取13位时间戳
     */
    public static function get13Timestamp() {
        list($t1, $t2) = explode(' ', microtime());
        return $t2.ceil($t1 * 1000);
    }
}