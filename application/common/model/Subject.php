<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/3
 * Time: 2:27
 */

namespace app\common\model;

class Subject extends Base {

    protected $updatetime = false;

    /**
     * 获取列表，分页
     */
    public function getAllByPage($condition = [], $from = 0, $size = 10)
    {
        $order = ['create_time' => 'DESC'];
        $news = $this
            ->alias('s')
            ->join('s_member m', 's.member_id=m.member_id')
            ->field('s.*,m.nick_name,m.head_img')
            ->where($condition)
            ->order($order)
            ->limit($from, $size)
            ->select();
        return $news;
    }

    // 获取单条
    public function getById($condition) {
        $order = ['create_time' => 'DESC'];
        $news = $this
            ->alias('s')
            ->join('s_member m', 's.member_id=m.member_id')
            ->field('s.*,m.nick_name,m.head_img')
            ->where($condition)
            ->order($order)
            ->find();
        return $news;
    }

    /* public function member()
    {
        return $this->hasOne('Profile');
    } */
}