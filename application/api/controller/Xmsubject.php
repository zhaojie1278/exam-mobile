<?php
namespace app\api\controller;

use think\Log;
use think\Session;

class Xmsubject extends Common
{
    private $done_xm_paper = array();

    // 已交卷判定
    private function isSubDone() {
        $m_paper = new \app\common\model\Xmsubpaper();
        $m_paper_whe['cid'] = $this->subject_cid;
        $m_paper_whe['uid'] = $this->uid;
        $xm_paper = $m_paper->getOne($m_paper_whe);
        $this->done_xm_paper = $xm_paper;
        if (!empty($xm_paper)) {
            return show_arr(config('code.error'), '您已提交试卷，不可重新做题或交卷', ['rs_subdone_url' => url('mobile/xmsubject/commitafter')], 200);
        } else {
            return show_arr(config('code.success'));
        }
    }

    // 做题
    public function dosub() {

        // 已交卷判定
        $rs_isdone = $this->isSubDone();
        if (config('code.error') == $rs_isdone['status']) {
            return json($rs_isdone, $rs_isdone['httpcode']);
        }

        $data = input('post.');
        try {
            $sub_id = $data['sub_id'] . '';
            $answer = $data['answer'] . '';
            $m_xm_subject = new \app\common\model\Xmsubject();
            $xm_sub_whe = ['id' => $sub_id];
            $xm_subject = $m_xm_subject->getOne($xm_sub_whe);

            $is_right = $xm_subject['check_answer'] == $answer ? 1 : 0;
            $m_paper_single = new \app\common\model\Xmsubpapersingle();
            $old_pap_whe = ['sub_id' => $sub_id, 'uid' => $this->uid];
            $m_old_paper = $m_paper_single->getOne($old_pap_whe);
            $rs = false;
            if ($m_old_paper) {
                $edit_data = array(
                    'id' => $m_old_paper['id'],
                    's_answer' => $xm_subject['check_answer'],
                    'score' => $xm_subject['score'],
                    'do_time' => time(),
                    'is_done' => 1,
                    'u_answer' => $answer,
                    'is_right' => $is_right,
                    // 'is_doned' => 1
                );
                $rs = $m_paper_single->edit($edit_data);
            } else {
                $add_data = array(
                    'sub_id' => $sub_id,
                    's_answer' => $xm_subject['check_answer'],
                    'uid' => $this->uid,
                    'score' => $xm_subject['score'],
                    'do_time' => time(),
                    'is_done' => 1,
                    'u_answer' => $answer,
                    'cid' => $xm_subject['cid'],
                    'is_right' => $is_right,
                    // 'is_doned' => 1
                );
                $rs = $m_paper_single->add($add_data);
            }
            if ($rs === false) {
                return show(config('code.error'), '提交失败，请稍后重试或联系管理员', [], 500);
            }
            $rs_data = ['u_id' => $this->uid, 'do_rs' => $rs];
        } catch(\Exception $e) {
            return show(config('code.error'), '系统异常，请联系管理员或稍后重试', [], 500);
        }
        return show(config('code.success'), 'OK', $rs_data, 200);
    }

    
    // 标注
    public function domark() {

        // 已交卷判定
        $rs_isdone = $this->isSubDone();
        if (config('code.error') == $rs_isdone['status']) {
            return json($rs_isdone, $rs_isdone['httpcode']);
        }

        $data = input('post.');
        try {
            $sub_id = $data['sub_id'] . '';
            $is_mark = $data['is_mark'] + 0;
            $m_xm_subject = new \app\common\model\Xmsubject();
            $xm_sub_whe = ['id' => $sub_id];
            $xm_subject = $m_xm_subject->getOne($xm_sub_whe);

            
            $m_paper_single = new \app\common\model\Xmsubpapersingle();
            $old_pap_whe = ['sub_id' => $sub_id, 'uid' => $this->uid];
            $m_old_paper = $m_paper_single->getOne($old_pap_whe);
            $rs = false;
            if ($m_old_paper) {
                $edit_data = array(
                    'id' => $m_old_paper['id'],
                    'is_mark' => $is_mark ? 0 : 1,
                    'mark_time' => time(),
                    // 'is_marked' => 1
                );
                $rs = $m_paper_single->edit($edit_data);
            } else {
                $is_right = $xm_subject['check_answer'] == '' ? 1 : 0;
                $add_data = array(
                    'sub_id' => $sub_id,
                    'uid' => $this->uid,
                    'cid' => $xm_subject['cid'],
                    'is_mark' => $is_mark ? 0 : 1,
                    'mark_time' => time(),
                    's_answer' => $xm_subject['check_answer'],
                    'score' => $xm_subject['score'],
                    'is_right' => $is_right,
                    // 'is_marked' => 1
                );
                $rs = $m_paper_single->add($add_data);
            }
            if ($rs === false) {
                return show(config('code.error'), '标注失败，请稍后重试或联系管理员', [], 500);
            }
            $rs_data = ['u_id' => $this->uid, 'do_rs' => $rs];
        } catch(\Exception $e) {
            return show(config('code.error'), '系统异常，请联系管理员或稍后重试'.$e->getLine(), [], 500);
        }
        return show(config('code.success'), 'OK', $rs_data, 200);
    }

    // 提交试卷前判定是否做完
    public function dosubcommitbefore () {

        // 已交卷判定
        $rs_isdone = $this->isSubDone();
        if (config('code.error') == $rs_isdone['status']) {
            return json($rs_isdone, $rs_isdone['httpcode']);
        }
        
        try {
            // 已做题目
            $m_single = new \app\common\model\Xmsubpapersingle();
            $m_paper_sg_whe['cid'] = $this->subject_cid;
            $m_paper_sg_whe['uid'] = $this->uid;
            $m_paper_sg_whe['u_answer'] = ['NEQ', ''];
            $do_subs = $m_single->getAll($m_paper_sg_whe);
            $do_sub_count = count($do_subs) + 0;
            
            // 总试题数量
            $m_sub = new \app\common\model\Xmsubject();
            $sub_where = ['cid' => $this->subject_cid];
            $sub_count = $m_sub->getCountByCondition($sub_where) + 0;

            Log::record('do_sub_count:'.$do_sub_count.'; sub_count:'.$sub_count);

            $undo_count = $sub_count - $do_sub_count;

            if ($do_sub_count != $sub_count) {
                return show(config('code.error'), '您还剩余 '.$undo_count.' 题未做，您确认交卷吗？', ['isprompt' => 1], 200);
            }
        } catch(\Exception $e) {
            Log::record('------->error:'.$e->getMessage());
            return show(config('code.error'), '系统异常，请联系管理员或稍后重试', [], 500);
        }
        return show(config('code.success'), 'OK');
    }
    
    // 提交试卷
    public function dosubcommit () {

        // 已交卷判定
        $rs_isdone = $this->isSubDone();
        if (config('code.error') == $rs_isdone['status']) {
            return json($rs_isdone, $rs_isdone['httpcode']);
        }
        
        try {
            // 已做试题
            $m_single = new \app\common\model\Xmsubpapersingle();
            $m_paper_sg_whe['cid'] = $this->subject_cid;
            $m_paper_sg_whe['uid'] = $this->uid;
            $do_subs = $m_single->getAll($m_paper_sg_whe);

            // 总试题数量
            $m_sub = new \app\common\model\Xmsubject();
            $sub_where = ['cid' => $this->subject_cid, 'is_deleted' => config('code.status_normal')];

            $columns = 'id,check_answer';
            $sub_id_answers = $m_sub->getAllColumns($sub_where, $columns);

            $analy_rs = $m_single->analyPaperSingles($do_subs);
            $m_paper = new \app\common\model\Xmsubpaper();

            // 考试花费时间
            $do_cost_time = 0;
            $subc_begin = $this->subject_class['begin_time'];
            $subc_end = $this->subject_class['end_time'];
            $do_subc_time = 0;
            $now_time = time();


            /* Log::record('$do_subc_time::'.$do_subc_time);
            Log::record('$subc_end::'.$subc_end);
            Log::record('$subc_begin::'.$subc_begin);
            Log::record('$now_time::'.$now_time); */

            // 最后一次阅读当前考卷须知的时间
            $m_notice_read = new \app\common\model\Xmsubjectnoticeread();
            $notice_whe = [];
            $notice_whe['subject_class_id'] = $this->subject_cid;
            $notice_whe['uid'] = $this->uid;
            $notice_whe['is_read'] = 1;
            $last_notice_read = $m_notice_read->getOne($notice_whe, 'id desc');
            $read_time = $last_notice_read['create_at'] ? strtotime($last_notice_read['create_at']) : 0;

            if ($subc_end != 0 && $subc_begin != 0) {
                if ($now_time <= $subc_end) {
                    $do_subc_end = $now_time;
                } else {
                    $do_subc_end = $subc_end;
                }

                // 考试须知阅读时间大于考试时间
                if ($subc_begin <= $read_time) {
                    $subc_begin = $read_time;
                }
                $do_subc_time = $do_subc_end - $subc_begin;
            }

            $paper_data = array(
                'sub_id' =>  $sub_id_answers ? json_encode(array_keys($sub_id_answers)) : null,
                's_answer' => $sub_id_answers ? json_encode($sub_id_answers) : null,
                'uid' => $this->uid,
                'time' => $do_subc_time,
                'u_answer' => $analy_rs['u_answers'],
                'score' => $analy_rs['sum_score'],
                'cid' => $this->subject_cid,
                'right_count' => $analy_rs['right_count'],
                'right_pre' => round($analy_rs['right_count'] / count($sub_id_answers), 2) * 100,
            );

            $rs = $m_paper->add($paper_data);
            if (!$rs) {
                return show(config('code.error'), '提交失败，请稍后重试或联系管理员', [], 500);
            }
            $rs_data = ['u_id' => $this->uid, 'do_rs' => $rs, 'a_href' => url('mobile/xmsubject/commitafter')];
        } catch(\Exception $e) {
            return show(config('code.error'), '系统异常，请联系管理员或稍后重试'.$e->getMessage(), [], 500);
        }
        return show(config('code.success'), 'OK', $rs_data, 200);
    }
}
