<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/3
 * Time: 2:27
 */

namespace app\common\model;

class Xmsubject extends Base {

    protected $table = 'xm_subject';
    protected $createTime = 'create_at';
    protected $autoWriteTimestamp = false;

    /**
     * 获取列表，分页
     */
    public function getAllByPage($condition = [])
    {
        $order = ['s.id' => 'DESC'];
        $subjects = $this
            ->alias('s')
            ->join('xm_subject_class c', 's.cid=c.id')
            ->field('s.*,c.name as class_name')
            ->where($condition)
            ->order($order)
            ->paginate(config('paginate.list_rows'),true);
        return $subjects;
    }

    // 获取单条
    public function getById($condition) {
        $order = ['s.id' => 'DESC'];
        $subject = $this
            ->alias('s')
            ->join('xm_subject_class c', 's.c_id=a.id')
            ->field('s.*,a.name as class_name')
            ->where($condition)
            ->order($order)
            ->find();
        return $subject;
    }
}