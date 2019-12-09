<?php
namespace app\index\controller;

class Index extends Common
{
    public function index()
    {
        $this->assign(['now_nav' => 'index', 'title' => '首页']);
    }
}
