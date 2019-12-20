<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/3
 * Time: 2:27
 */

namespace app\common\model;

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
        $s_answers = array();
        $u_answers = array();
        $sum_score = 0;
        $right_pre = 0;
        foreach ($paper_singles as $pap_s) {
            $sub_ids[] = $pap_s['sub_id'];
            $s_answers[$pap_s['sub_id']] = $pap_s['s_answer'];
            $u_answers[$pap_s['sub_id']] = $pap_s['u_answer'];
            if ($pap_s['s_answer'] == $pap_s['u_answer']) {
                // 答案正确
                $sum_score += $pap_s['score'];
                $right_pre += 1;
            }
        }

        // $sub_ids = $sub_ids ? json_encode($sub_ids) : null;
        $s_answers = $s_answers ? json_encode($s_answers) : null;
        $u_answers = $u_answers ? json_encode($u_answers) : null;
        $rs = array(
            // 'sub_ids' => $sub_ids,
            's_answers' => $s_answers,
            'u_answers' => $u_answers,
            'sum_score' => $sum_score,
            'right_pre' => $right_pre,
        );
        return $rs;
    }
}
