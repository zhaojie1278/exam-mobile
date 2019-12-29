<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/3
 * Time: 2:27
 */

namespace app\common\model;

use think\Log;

class Xmsubpapersingle extends Base
{
    protected $table = 'xm_subject_paper_single';
    protected $createTime = 'create_at';
    // protected $autoWriteTimestamp = false;

    
    // 分析已答试题
    public function analyPaperSingles($paper_singles)
    {
        if (empty($paper_singles)) {
            return array();
        }

        // $sub_ids = array();
        // $s_answers = array();
        $u_answers = array();
        $sum_score = 0;
        // $right_pre = 0;
        $right_count = 0;
        foreach ($paper_singles as $pap_s) {
            // $sub_ids[] = $pap_s['sub_id'];
            // $s_answers[$pap_s['sub_id']] = $pap_s['s_answer'];
            if ($pap_s['u_answer'] !== '') {
                $u_answers[$pap_s['sub_id']] = $pap_s['u_answer'];
                if ($pap_s['s_answer'] == $pap_s['u_answer']) {
                    // 答案正确
                    $sum_score += $pap_s['score'];
                    // $right_pre += 1;
                    $right_count += 1;
                }
            }
        }

        // $sub_ids = $sub_ids ? json_encode($sub_ids) : null;
        // $s_answers = $s_answers ? json_encode($s_answers) : null;
        $u_answers = $u_answers ? json_encode($u_answers) : null;
        $rs = array(
            // 'sub_ids' => $sub_ids,
            // 's_answers' => $s_answers,
            'u_answers' => $u_answers,
            'sum_score' => $sum_score,
            // 'right_pre' => $right_pre,
            'right_count' => $right_count,
        );
        return $rs;
    }

    // 生成个人试卷
    public function generateMemberSPSingle($sub_all, $uid, $subject_class_cid, $is_rand) {

        if (empty($sub_all) || count($sub_all) == 0 || empty($uid) || empty($subject_class_cid)) {
            return false;
        }

        $add_data = array();

        $add_subs = array();

        // 使用题干id随机，保证有题干的题目还是在一起展示
        $sub_stem_ids = [];
        $sub_stem_all_kv = [];
        foreach($sub_all as $sub) {
            if (in_array($sub['sub_stem_id'], $sub_stem_ids)) {
                $sub_stem_all_kv[$sub['sub_stem_id']][] = $sub;
            } else {
                $sub_stem_all_kv[$sub['sub_stem_id']][] = $sub;
                $sub_stem_ids[] = $sub['sub_stem_id'];
            }
        }
        if ($sub_stem_ids) {
            if ($is_rand) {
                // 打乱试题
                shuffle( $sub_stem_ids );
            }
        }

        $sub_order_i = 1;
        Log::record('$sub_stem_ids::'.var_export(count($sub_stem_ids), true));
        Log::record('$sub_stem_ids::'.var_export($sub_stem_ids, true));
        foreach($sub_stem_ids as $sub_stem_id) {
            $subs = $sub_stem_all_kv[$sub_stem_id];

            // 一个题干的多个题目，如果没有多个，至少有一个
            foreach ($subs as $sub) {
                $sub_id = $sub['id'];
                $old_pap_whe = ['sub_id' => $sub_id, 'uid' => $uid, 'cid' => $subject_class_cid];
                $m_old_paper_s = $this->getOne($old_pap_whe);
                $sub_check_answer = $sub['check_answer'];
                $sub_score = $sub['score'];
                $sub_cid = $sub['cid'];
                if (empty($m_old_paper_s)) {
                    $add_data[] = array(
                        'sub_order_i' => $sub_order_i,
                        'sub_id' => $sub['id'],
                        's_answer' => $sub_check_answer,
                        'uid' => $uid,
                        'score' => $sub_score,
                        'cid' => $sub_cid,
                        'sub_stem_id' => $sub['sub_stem_id'],
                    );
                    $sub_order_i++;
                } else {
                    /* $edit_data = array(
                         'id' => $m_old_paper_s['id'],
                         's_answer' => $sub_check_answer,
                         'uid' => $uid,
                         'score' => $$sub_score,
                         'cid' => $sub_cid,
                     );
                     $rs = $this->edit($edit_data); */
                }
            }
        }

        if ($add_data) {
            $rs = $this->addAll($add_data);
        } else {
            $rs = true;
        }
        return $rs;
    }
}
