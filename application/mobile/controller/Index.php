<?php
namespace app\mobile\controller;

use think\Log;

class Index extends Common
{
    public function index()
    {
        // dump($_SERVER);
        // EXIT;
        $data = input('get.');
        try {
            $this->getPageAndSize($data);
            $where['check_status'] = ['EQ', config('code.check_pass')];
            $m_shuoshuo = new \app\common\model\Shuoshuo();
            $shuoshuos = $m_shuoshuo->getAllByPage($where, $this->from, $this->size);
            $this->assign(['list' => $shuoshuos]);
        } catch (\Exception $e) {
            Log::error('shuoshuo----->'.$e->getCode().':'.$e->getMessage());
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

    public function detail()
    {
        $s_id = input('get.s_id', '0', 'int');
        try {
            $where['shuo_id'] = $s_id;
            $where['check_status'] = ['EQ', config('code.check_pass')];
            $m_shuoshuo = new \app\common\model\Shuoshuo();
            $shuoshuo = $m_shuoshuo->getById($where);
            $this->assign(['shuoshuo' => $shuoshuo]);
        } catch (\Exception $e) {
            Log::error('shuoshuo----->' . $e->getCode() . ':' . $e->getMessage());
            $this->error('查询失败，请联系管理员', 'Error/error404');
        }
        return $this->fetch();
    }
}
