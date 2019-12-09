<?php
namespace app\mobile\controller;

class Error extends Common
{
    public function error404()
    {
        return $this->fetch();
    }
}
