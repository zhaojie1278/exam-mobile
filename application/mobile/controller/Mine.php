<?php
namespace app\mobile\controller;

class Mine extends Common 
{
    public function index() {
        return $this->fetch();
    }
}