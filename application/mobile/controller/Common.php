<?php
namespace app\mobile\controller;

use think\Controller;

class Common extends Controller
{
    public $mobile_index_url = '';
    // 分页相关
    public $page_index = 1;
    public $size = 5;
    public $from = 0;

    public function _initialize()
    {
        parent::_initialize();
        $this->mobile_index_url = '/mobile';
        $this->assign('mobile_index_url', $this->mobile_index_url);
    }

    public function getPageAndSize($data)
    {
        $this->page_index = !empty($data['page_index']) ? $data['page_index'] : 1;
        $this->size = !empty($data['size']) ? $data['size'] : config('paginate.list_rows');
        $this->from = ($this->page_index - 1) * $this->size;
    }
}
