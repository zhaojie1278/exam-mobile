<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

class Common extends Controller
{
    // 分页相关
    public $page = 1;
    public $size = 5;
    public $from = 0;

    public function _initialize()
    {
        $company = model('common/appinfo')->find();
        parent::_initialize();
    }

    public function getPageAndSize($data)
    {
        $this->page = !empty($data['page']) ? $data['page'] : 1;
        $this->size = !empty($data['size']) ? $data['size'] : config('paginate.list_rows');
        $this->from = ($this->page - 1) * $this->size;
    }
}
