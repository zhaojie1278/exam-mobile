<?php
namespace app\api\controller;

use think\Log;
use think\Controller;
use think\Session;

class Login extends Controller
{
    // 登录
    public function dologin() {
        $data = input('post.');
        try {
            $class_no = $data['class_no'] . '';
            $real_namne = $data['real_name'] . '';
            $phone = $data['phone'] . '';
            if (strlen($phone) != 11) {
                return show(config('code.error'), '请输入正确的手机号', [], 200);
            }
            $subject_cid = $data['cid'] + 0;
            $m_xm_member = new \app\common\model\Xmmember();
            $xm_sub_whe = [
                'class_no' => $class_no,
                'real_name' => $real_namne,
                'is_deleted' => config('code.status_normal'),
                'status' => config('code.user_status_normal')
            ];
            $xm_subject = $m_xm_member->getOne($xm_sub_whe);
            if (empty($xm_subject['class_no'])) {
                return show(config('code.error'), '登录失败，请校正信息后重新登录', [], 200);
            } else {
                // dump(Session::get('member'));
                $nowtime = time();
                $rs = $m_xm_member->edit(['id' => $xm_subject->id, 'login_time' => $nowtime, 'phone' => $phone]);

                // 登录日志
                $m_xm_member_login = new \app\common\model\Xmmemberlogin();
                $rs_login_add = $m_xm_member_login->add(['uid' => $xm_subject->id, 'login_time' => $nowtime, 'phone' => $phone, 'class_no' => $class_no, 'real_name' => $real_namne, 'subject_cid' => $subject_cid]);

                if ($rs === false || !$rs_login_add) {
                    throw new \think\Exception('登录信息保存失败', 100006);
                }

                // 考试时间管理（根据试卷）
                $m_subject_class = new \app\common\model\Xmsubjectclass();
                $sub_class_whe = ['id' => $subject_cid, 'is_deleted' => config('code.status_normal')];
                $subject_class = $m_subject_class->getOne($sub_class_whe);
                $subc_begin = $subject_class['begin_time'];
                $subc_end = $subject_class['end_time'];

                if (empty($subject_class['id'])) {
                    return show(config('code.error'), '考试信息不存在', [], 200);
                }

                if ($nowtime < $subc_begin) {
                    return show(config('code.error'), '考试尚未开始', [], 200);
                }

                if ($nowtime >= $subc_end) {
                    return show(config('code.error'), '考试已结束', [], 200);
                }

                // session 管理
                Session::delete('member');
                $session_member = [
                    'uid' => $xm_subject->id,
                    'real_name' => $class_no,
                    'phone' => $phone,
                    'login_time' => $nowtime,
                    'subject_cid' => $subject_cid
                ];
                Session::set('member', $session_member);
            }
            
            $rs_data = ['re_href' => url('mobile/xmsubject/index')];
        } catch(\Exception $e) {
            Log::record('error->'.$e->getMessage());
            return show(config('code.error'), '登录系统异常，请联系管理员或稍后重试', [], 500);
        }
        return show(config('code.success'), 'OK', $rs_data, 200);
    }

    
    // 标注
    private function generate_subject() {
    }
}
