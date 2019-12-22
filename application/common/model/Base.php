<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/3
 * Time: 2:27
 */

namespace app\common\model;

use think\Model;

class Base extends Model {

    protected $autoWriteTimestamp = true;

    /**
     * 保存多条数据
     * @param array $list
     * @return 
     */
    public function addAll($list) {
        return $this->allowField(true)->saveAll($list);
    }
    /**
     * 新增
     * @param $data
     * @param null $dupfield
     * @param null $dupmsg
     * @return mixed
     */
    public function add($data, $dupfield = null, $dupmsg = null) {
        if (!$data) {
            exception('传输数据不合法');
        }
        // 重名判断
        /* if ($dupfield) {
            $getRs = $this->get([$dupfield => $data[$dupfield], 'status' => config('code.status_normal')]);
            if ($getRs) {
                exception($dupmsg.'，添加失败！');
            }
        } */
        $this->allowField(true)->save($data);
        return $this->id;
    }
    /**
     * 修改
     * @param $data
     * @return mixed
     */
    public function edit($data, $dupfield = null, $dupmsg = null) {
        if (!$data || empty($data['id'])) {
            exception('传输数据不合法');
        }
        // 重名判断
        /* if ($dupfield) {
            $getRs = $this->get([$dupfield => $data[$dupfield], 'status' => config('code.status_normal'),'id'=>['NEQ',$data['id']]]);
            if ($getRs) {
                exception($dupmsg.'，修改失败！');
            }
        } */
        $rs = $this->allowField(true)->save($data,['id'=>$data['id']]);
        return $rs;
    }

    /**
     * 删除
     * @param $data
     * @return false|int
     */
    public function del($data, $isPhysical = false) {
        if (!$data || (empty($data['id']) && empty($data['ids']))) {
            exception('传输数据不合法');
        }
        $rs = false;
        if ($isPhysical) {
            if (!empty($data['id'])) {
                $rs = $this->destroy($data['id']);
            }
        } else {
            if (!empty($data['id'])) {
                $updata = ['status' => config('code.status_delete')];
                $rs = $this->allowField(true)->save($updata, ['id' => $data['id']]);
            } else if (!empty($data['ids'])) {
                $updata['status'] = config('code.status_delete');
                $updata['update_time'] = date('Y-m-d H:i:s');
                $rs = $this->allowField(true)->where('id', 'IN', $data['ids'])->update($updata);
            }
        }
        return $rs;
    }

    /**
     * 获取总数
     */
    public function getCount($statusField = true) {
        if ($statusField) {
            $where = ['is_deleted'=>config('code.status_normal')];
        } else {
            $where = [];
        }
        $count = 0;
        if ($where) {
            $count = $this->where($where)->count('id');
        } else {
            $count = $this->where()->count('id');
        }
        return $count;
    }

    /**
     * 获取单条
     */
    public function getOne($where, $order = '')
    {
        if ($order) {
            $one = $this->where($where)->order($order)->find();
        } else {
            $one = $this->where($where)->find();
        }
        return $one;
    }

    // 获取所有
    public function getAll($where, $fields = '') {
        if ($fields) {
            $all_d = $this->where($where)->field($fields)->select();
        } else {
            $all_d = $this->where($where)->select();
        }
        return $all_d;
    }

    

    // 获取所有ID
    public function getAllIds($where, $id_field = 'id') {
        $all_d = $this->where($where)->column($id_field);
        return $all_d;
    }

    // 获取指定 column
    public function getAllColumns($where, $columns = 'id') {
        $all_d = $this->where($where)->column($columns);
        return $all_d;
    }

    
    /**
     * 获取总数
     */
    public function getCountByCondition($where, $statusField = true) {
        if ($statusField) {
            $where['is_deleted'] = config('code.status_normal');
        }
        $count = $this->where($where)->count('id');
        return $count;
    }
}