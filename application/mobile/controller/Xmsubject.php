<?php
namespace app\mobile\controller;

use think\Log;

class Xmsubject extends Common
{
    public function index()
    {
        $uid = 990; // TODO
        try {
            $where['s.is_deleted'] = ['EQ', config('code.status_normal')];
            $where['s.cid'] = config('subject.cid_thisone');
            $m_subject = new \app\common\model\Xmsubject();
            $subjects = $m_subject->getAllByPage($where, $uid);
            $this->assign(['list' => $subjects]);
            $this->assign('member', array('uid' => $uid));
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
}
