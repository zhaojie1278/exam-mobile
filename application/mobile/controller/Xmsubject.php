<?php
namespace app\mobile\controller;

use think\Log;

class Xmsubject extends Common
{
    public function index()
    {
        $data = input('get.');
        try {
            $this->getPageAndSize($data);
            $where['s.is_deleted'] = ['EQ', config('code.status_normal')];
            $m_subject = new \app\common\model\Xmsubject();
            $subjects = $m_subject->getAllByPage($where);
            $this->assign(['list' => $subjects]);
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
