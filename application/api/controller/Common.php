<?php
namespace app\api\controller;

use think\Controller;
use think\Session;

/**
 * API模块公共控制器
 */
class Common extends Controller {

    // 分页相关
    public $page = 1;
    public $size = 5;
    public $from = 0;

    public $uid = 0; // TODO session

    /**
     * Header 头
     */
    public $headers = [];

    /**
     * 初始化
     */
    public function _initialize() {
        // $this->checkRequestAuth(); // todo
        // $this->testAes();

        if (Session::get('member.uid')) {
            $this->uid = Session::get('member.uid');
        } else {
            echo json_encode(show_arr(config('code.error'), '您尚未登录，请登录后操作', 
                ['rs_login_url' => url('mobile/login/index')], 200));
                exit;
        }
    }

    /**
     * 校验数据是否合法
     */
    public function checkRequestAuth() {
        // 获取header version/sign/did 等

        // Time::get13Timestamp();

        return true;
    }

    // 设置分页数据
    public function getPageAndSize($data) {
        $this->page = !empty($data['page']) ? $data['page'] : 1;
        $this->size = !empty($data['size']) ? $data['size'] : config('paginate.list_rows');
        $this->from = ($this->page - 1) * $this->size;
    }
}