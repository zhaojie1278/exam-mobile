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

    public $uid = 0;
    public $subject_cid = 0;
    public $subject_class = [];

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
            $this->subject_cid = Session::get('member.subject_cid');

            if ($this->subject_cid) {
                $m_sub_class = new \app\common\model\Xmsubjectclass();
                $sub_cla_whe = ['id' => $this->subject_cid];
                $sub_class = $m_sub_class->getOne($sub_cla_whe);
                $this->subject_class = $sub_class;
            }
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