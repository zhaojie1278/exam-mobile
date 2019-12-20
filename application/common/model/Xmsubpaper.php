<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/3
 * Time: 2:27
 */

namespace app\common\model;

class Xmsubpaper extends Base {

    protected $table = 'xm_subject_paper';
    protected $createTime = 'create_at';
    // protected $autoWriteTimestamp = false;
    protected $autoWriteTimestamp = 'datetime';
    
    // 获取单条
}