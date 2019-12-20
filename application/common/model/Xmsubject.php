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
    // protected $createTime = 'create_at';
    // protected $autoWriteTimestamp = false;

    /**
     * 获取列表，分页
     */
    public function getAllByPage($condition = [], $uid)
    {
        $order = ['s.id' => 'DESC'];
        $subjects = $this
            ->alias('s')
            ->join('xm_subject_class c', 's.cid=c.id')
            ->join('xm_subject_paper_single ps', "s.id=ps.sub_id and ps.uid='$uid'", "LEFT")
            ->field('s.*,c.name as class_name,ps.uid,ps.u_answer')
            ->where($condition)
            ->order($order)
            ->paginate(config('paginate.list_rows'),true);
        return $subjects;
    }

    // 获取单条
    public function getById($condition) {
        $subject = $this
            ->where($condition)
            ->find();
        return $subject;
    }
}