<?php
namespace app\mobile\controller;

use think\Log;
use think\Controller;

class Login extends Controller
{
    public function index() {
        // 考试试卷
        $m_sub_class = new \app\common\model\Xmsubjectclass();

        $sc_whe = [
            'end_time' => ['GT', time() - 600],
            'is_deleted' => config('code.status_normal')
        ];
        $subject_class_list = $m_sub_class->getAllClass($sc_whe);
        $this->assign('subject_class_list', $subject_class_list);
        return $this->fetch();
    }
}
