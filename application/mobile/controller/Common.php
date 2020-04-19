<?php
namespace app\mobile\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\Log;

class Common extends Controller
{

    public $uid = 0;
    public $subject_cid = 0;
    public $subject_class = array();
    public $mobile_index_url = '';
    // 分页相关
    public $page_index = 1;
    public $size = 5;
    public $from = 0;

    public function _initialize()
    {
        parent::_initialize();
        Log::record('--------_initialize time::'.time());
        $this->mobile_index_url = '/mobile';
        if (Session::get('member.uid')) {
            $this->uid = Session::get('member.uid');
            $this->subject_cid = Session::get('member.subject_cid');
        } else {
            // 未登录
            $this->redirect('mobile/login/index');
        }
        Log::record('--------_initialize::'.var_export($this->uid, true));
        
        $this->assign('mobile_index_url', $this->mobile_index_url);

        // 考试时间管理（根据试卷）
        if ($this->subject_cid) {
            $m_sub_class = new \app\common\model\Xmsubjectclass();
            $subc_whe = ['id' => $this->subject_cid, 'is_deleted' => config('code.status_normal')];
            $_sub_class = $m_sub_class->getOne($subc_whe);
            $this->subject_class = $_sub_class;
            $nowtime = time();
            $subc_begin = $_sub_class['begin_time'];
            $subc_end = $_sub_class['end_time'];

            if (empty($_sub_class['id'])) {
                Session::delete('member');
                $this->success('考试信息不存在', 'mobile/login/index');
            }

            if ($nowtime < $subc_begin) {
                Session::delete('member');
                $this->success('考试尚未开始', 'mobile/login/index');
            }

            // 考试结果页不控制
            $request = Request::instance();
            $cur_controller = $request->controller();
            $cur_action = $request->action();
            
            if (!(strtolower($cur_controller) == 'xmsubject' && (strtolower($cur_action) == 'commitafter' || strtolower($cur_action) == 'xmsubjectexport'))) {
                if ($nowtime >= $subc_end) {
                    Session::delete('member');
                    $this->success('考试已结束', 'mobile/login/index');
                }   
            }

            $reminder_time = $subc_end - $nowtime;
            if ($reminder_time <= 0) {
                $reminder_time = 0;
            }
            $reminder_time_str = time_remainder($reminder_time);
            Log::record('--------uid::'.var_export($this->uid, true));
            Log::record('--------reminder_time::'.var_export($reminder_time, true));
            Log::record('--------reminder_time_str::'.var_export($reminder_time_str, true));
            $this->assign('reminder_time', $reminder_time);
            $this->assign('reminder_time_str', $reminder_time_str);
            $this->assign('page_title', $_sub_class['name']);
        }
        
    }

    public function getPageAndSize($data)
    {
        $this->page_index = !empty($data['page_index']) ? $data['page_index'] : 1;
        $this->size = !empty($data['size']) ? $data['size'] : config('paginate.list_rows');
        $this->from = ($this->page_index - 1) * $this->size;
    }
}
