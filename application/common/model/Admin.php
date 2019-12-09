<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/3
 * Time: 2:27
 */

namespace app\common\model;

class Admin extends Base {

    protected $updateTime = false;

    /**
     * 新增
     * @param $data
     * @return mixed
     */
    public function add($data,$f=null,$m=null) {
        return parent::add($data,'username','用户 '.$data['username'].' 已存在');
    }
}