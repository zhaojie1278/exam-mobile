<?php
namespace app\mobile\controller;

use think\Log;

class Index extends Common
{
    public function index() {
        return $this->fetch();
    }
}
