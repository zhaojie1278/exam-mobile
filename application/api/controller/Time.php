<?php
namespace app\api\controller;

use think\Controller;

/**
 * APP端与服务端时间不一致处理
 */
class Time extends Controller {
    public function index() {
        return show(1, 'OK', time());
    }
}