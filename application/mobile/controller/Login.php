<?php
namespace app\mobile\controller;

use think\Log;
use think\Controller;

class Login extends Controller
{
    public function index() {
        return $this->fetch();
    }
}
