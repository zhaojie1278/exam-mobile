<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/3
 * Time: 2:27
 */

namespace app\common\model;

class Xmsubjectclass extends Base
{
    protected $table = 'xm_subject_class';
    // protected $createTime = 'create_at';
    // protected $autoWriteTimestamp = false;

    public function getAllClass($where, $fields = '') {
        $data = $this->where($where)->order('id desc')->select();
        return $data;
    }
}
