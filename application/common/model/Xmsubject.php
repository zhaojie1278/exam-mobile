<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/3
 * Time: 2:27
 */

namespace app\common\model;

class Xmsubject extends Base
{
    protected $table = 'xm_subject';
    // protected $createTime = 'create_at';
    // protected $autoWriteTimestamp = false;

    /**
     * 获取列表，分页
     */
    public function getAllByPage($condition = [], $uid, $page_config = array(), $where_query = null)
    {
        $order = ['ps.id' => 'ASC'];
        if (!empty($where_query)) {
            $subjects = $this
            ->alias('s')
            ->join('xm_subject_class c', 's.cid=c.id')
            ->join('xm_subject_paper_single ps', "s.id=ps.sub_id and ps.uid='$uid'")
            ->field('s.*,c.name as class_name,ps.uid,ps.u_answer,ps.is_mark,ps.sub_order_i')
            ->where($condition)
            ->where(function($query) use ($where_query){
                $query->where($where_query);
            })
            ->order($order)
            ->paginate(config('paginate.list_rows'), true, $page_config);
        } else {
            $subjects = $this
            ->alias('s')
            ->join('xm_subject_class c', 's.cid=c.id')
            ->join('xm_subject_paper_single ps', "s.id=ps.sub_id and ps.uid='$uid'")
            ->field('s.*,c.name as class_name,ps.uid,ps.u_answer,ps.is_mark,ps.sub_order_i')
            ->where($condition)
            ->order($order)
            ->paginate(config('paginate.list_rows'), true, $page_config);
        }
        
        return $subjects;
    }

    // 获取单条
    public function getById($condition)
    {
        $subject = $this
            ->where($condition)
            ->find();
        return $subject;
    }

    // 根据选项从选项集中获取选项详情
    public function getSubOption($answers, $option) {
        $answers = json_decode($answers, true);
        $answer_txt = '';
        foreach($answers as $k => $v) {
            if ($option == $v['a']) {
                $answer_txt = $v['t'];
                break;
            }
        }
        return $answer_txt;
    }
}
