<?php
namespace app\api\controller;
class Index extends Common {

    /**
     * 
     */
    public function index() {
        $data = [];
        return show(config('code.success'), 'OK', ['list'=> $data], 200);
    }
}