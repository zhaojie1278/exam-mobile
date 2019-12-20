<?php
namespace app\mobile\controller;

use think\Log;

class Xmsubject extends Common
{
    private $done_xm_paper = array();
    
    // 已交卷判定
    private function isSubDone() {
        $m_paper = new \app\common\model\Xmsubpaper();
        $m_paper_whe['cid'] = config('subject.cid_thisone');
        $m_paper_whe['uid'] = $this->uid;
        $xm_paper = $m_paper->getOne($m_paper_whe);
        $this->done_xm_paper = $xm_paper;
        if (!empty($xm_paper)) {
            $this->success('您已交卷', 'mobile/xmsubject/commitafter');
        }
    }

    public function index()
    {

        // 查看是否已交卷
        $this->isSubDone();
        
        try {
            $where['s.is_deleted'] = ['EQ', config('code.status_normal')];
            $where['s.cid'] = config('subject.cid_thisone'); // TODO 配置
            $m_subject = new \app\common\model\Xmsubject();
            $subjects = $m_subject->getAllByPage($where, $this->uid);
            $this->assign(['list' => $subjects]);
            $this->assign('member', array('uid' => $this->uid));
        } catch (\Exception $e) {
            Log::error('subject----->'.$e->getCode().':'.$e->getMessage());
            $this->error('查询失败，请联系管理员', 'Error/error404');
        }

        // dump($this->page_index);
        if ($this->page_index > 1) {
            $this->view->engine->layout(false);
            return $this->fetch('index_page');
        } else {
            return $this->fetch();
        }
    }

    // 提交试卷
    public function commitafter() {
        // 试卷题目是否做完

        
        return $this->fetch();
    }
}
