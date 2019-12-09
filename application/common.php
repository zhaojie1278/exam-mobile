<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function setFilePathRandTimeStr() {
    $rand = rand(100000,999999);
    // date('Ymd') . DS . md5(microtime(true))
    list($t1, $t2) = explode(' ', microtime());
    // return date('YmdHi') . DS . md5($t2.ceil($t1 * 1000).$rand);
    return md5(date('YmdHi').$t2.ceil($t1 * 1000).$rand);
}

/**
 * 通用化API接口输出
 * @param int $status 业务状态码
 */
function show($status, $message, $data = [], $httpcode = 200) {
    $data = [
        'status' => $status,
        'message' => $message,
        'data' => $data
    ];
    return json($data, $httpcode);
}

/** 
 * 计算几分钟前、几小时前、几天前、几月前、几年前。 
 * $agoTime string Unix时间 
 * @author tangxinzhuan 
 * @version 2016-10-28 
 */  
function time_ago($agoTime)  
{  
    $agoTime = (int)$agoTime;  
      
    // 计算出当前日期时间到之前的日期时间的毫秒数，以便进行下一步的计算  
    $time = time() - $agoTime;  
      
    if ($time >= 31104000) { // N年前  
        $num = (int)($time / 31104000);  
        return $num.'年前';  
    }  
    if ($time >= 2592000) { // N月前  
        $num = (int)($time / 2592000);  
        return $num.'月前';  
    }  
    if ($time >= 86400) { // N天前  
        $num = (int)($time / 86400);  
        return $num.'天前';  
    }  
    if ($time >= 3600) { // N小时前  
        $num = (int)($time / 3600);  
        return $num.'小时前';  
    }  
    if ($time > 60) { // N分钟前  
        $num = (int)($time / 60);  
        return $num.'分钟前';  
    }  
    return '1分钟前';  
}  
