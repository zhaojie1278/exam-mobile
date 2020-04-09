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
            $class_no = trim($data['class_no']) . '';
            $real_namne = trim($data['real_name']) . '';
            $phone = trim($data['phone']) . '';
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
            $member_info = $m_xm_member->getOne($xm_sub_whe);
            if (empty($member_info['class_no'])) {
                return show(config('code.error'), '登录失败，请校正信息后重新登录', [], 200);
            } else {

                // 验证手机号
                if (!empty($member_info['phone']) && $member_info['phone'] != $phone) {
                    return show(config('code.error'), '登录失败，请输入正确的手机号'.encrypt_sub_phone($member_info['phone']).'登录', [], 200);
                }
                // dump(Session::get('member'));

                $uid = $member_info->id;
                $nowtime = time();
                $rs = $m_xm_member->edit(['id' => $uid, 'login_time' => $nowtime, 'phone' => $phone]);

                // 登录日志
                $m_xm_member_login = new \app\common\model\Xmmemberlogin();
                $rs_login_add = $m_xm_member_login->add(
                    [
                        'uid' => $uid, 
                        'login_time' => $nowtime, 
                        'phone' => $phone, 
                        'class_no' => $class_no, 
                        'real_name' => $real_namne, 
                        'subject_cid' => $subject_cid,
                        'ip' => get_ip()
                    ]
                );

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

                $is_end_enter = false;
                if ($nowtime >= $subc_end) {
                    $is_end_enter = true;
                } else {
                    // 生成带排序的试卷
                    $m_sub = new \app\common\model\Xmsubject();
                    $sub_where = ['cid' => $subject_class['id'], 'is_deleted' => config('code.status_normal')];
                    $fields = 'id,check_answer,score,cid,sub_stem_id,sub_stem_id,sub_stem';
                    $sub_all = $m_sub->getAll($sub_where, $fields);
                    $m_sub_pap_single = new \app\common\model\Xmsubpapersingle();
                    $rs_generate = $m_sub_pap_single->generateMemberSPSingle($sub_all, $uid, $subject_class['id'], $subject_class['is_rand']);
                    if (!$rs_generate) {
                        return show(config('code.error'), '考题生成失败，请联系管理员或稍后重试~', [], 200);
                    }
                }

                // session 管理
                Session::delete('member');
                $session_member = [
                    'uid' => $uid,
                    'real_name' => $class_no,
                    'phone' => $phone,
                    'login_time' => $nowtime,
                    'subject_cid' => $subject_cid
                ];
                Session::set('member', $session_member);
                
                if ($is_end_enter) {
                    $m_paper = new \app\common\model\Xmsubpaper();
                    $m_paper_whe['cid'] = $subject_cid;
                    $m_paper_whe['uid'] = $uid;
                    $xm_paper = $m_paper->getOne($m_paper_whe);
                    if (!empty($xm_paper)) {
                        // $this->success('您已交卷，将为您自动跳转', 'mobile/xmsubject/commitafter');
                        return show(config('code.error'), '考试已结束，点击确定查看考试结果', ['re_href' => url('mobile/xmsubject/commitafter')], 200);
                    } else {
                        return show(config('code.error'), '考试已结束', [], 200);
                    }
                } else {
                    $rs_data = ['re_href' => url('mobile/xmsubject/index')];
                }
            }
            
        } catch(\Exception $e) {
            Log::record('error->'.$e->getMessage().'---'.$e->getLine());
            return show(config('code.error'), '登录系统异常，请联系管理员或稍后重试', [], 500);
        }
        return show(config('code.success'), 'OK', $rs_data, 200);
    }
}
