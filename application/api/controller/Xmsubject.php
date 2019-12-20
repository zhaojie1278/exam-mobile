<?php
namespace app\api\controller;

use think\Log;

class Xmsubject extends Common
{
    public function dosub() {
        $data = input('post.');
        try {
            $sub_id = $data['sub_id'] . '';
            $answer = $data['answer'] . '';
            $u_id = $data['u_id'] + 0;
            $m_xm_subject = new \app\common\model\Xmsubject();
            $xm_sub_whe = ['id' => $sub_id];
            $xm_subject = $m_xm_subject->getOne($xm_sub_whe);

            $is_right = $xm_subject['check_answer'] == $answer ? 1 : 0;
            $add_data = array(
                'sub_id' => $sub_id,
                's_answer' => $xm_subject['check_answer'],
                'uid' => $u_id,
                'score' => $xm_subject['score'],
                'do_time' => time(),
                'u_answer' => $answer,
                'cid' => $xm_subject['cid'],
                'is_right' => $is_right
            );
            $m_paper_single = new \app\common\model\Xmsubpapersingle();
            $old_pap_whe = ['sub_id' => $sub_id, 'uid' => $u_id];
            $m_old_paper = $m_paper_single->getOne($old_pap_whe);
            $rs = false;
            if ($m_old_paper) {
                $add_data['id'] = $m_old_paper['id'];
                $rs = $m_paper_single->edit($add_data);
            } else {
                $rs = $m_paper_single->add($add_data);
            }
            if ($rs === false) {
                return show(config('code.error'), '提交失败，请稍后重试或联系管理员', [], 500);
            }
            $rs_data = ['sub_id' => $sub_id, 'u_id' => $u_id, 'do_rs' => $rs];
        } catch(\Exception $e) {
            return show(config('code.error'), $e->getMessage(), [], 500);
        }
        return show(config('code.success'), 'OK', ['list'=> $rs_data], 200);
    }

    // 提交试卷
    public function dosuball () {

    }
}
