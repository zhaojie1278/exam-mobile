<?php
namespace app\api\controller;

use think\Controller;

/**
 * API模块公共控制器
 */
class Common extends Controller {

    // 分页相关
    public $page = 1;
    public $size = 5;
    public $from = 0;

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