<?php
namespace app\mobile\controller;

use think\Db;
use think\Log;
use think\Session;

class Xmsubject extends Common
{
    private $done_xm_paper = array();
    
    // 已交卷判定
    private function isSubDone() {
        $m_paper = new \app\common\model\Xmsubpaper();
        $m_paper_whe['cid'] = $this->subject_cid;
        $m_paper_whe['uid'] = $this->uid;
        $xm_paper = $m_paper->getOne($m_paper_whe);
        $this->done_xm_paper = $xm_paper;
        if (!empty($xm_paper)) {
            $this->success('您已交卷，将为您自动跳转', 'mobile/xmsubject/commitafter');
        }
    }
    
    // 考试须知是否阅读
    private function isReadNotice() {
        $xm_notice = $this->getXmnotice();
        if (!empty($xm_notice->id)) {
            $m_notice_read = new \app\common\model\Xmsubjectnoticeread();
            $m_notice_read_whe['subject_class_id'] = $this->subject_cid;
            $m_notice_read_whe['uid'] = $this->uid;
            $m_notice_read_whe['is_read'] = 1;
            $m_notice_read_whe['notice_id'] = $xm_notice->id;
            $xm_notice_read = $m_notice_read->getOne($m_notice_read_whe);
            if (empty($xm_notice_read)) {
                $this->redirect('mobile/xmsubject/notice');
            }
        }        
    }

    // 增加阅读考试须知信息
    private function readNotice($notice_id) {
        
        $m_notice_read = new \app\common\model\Xmsubjectnoticeread();
        $read_data['subject_class_id'] = $this->subject_cid;
        $read_data['uid'] = $this->uid;
        $read_data['is_read'] = 1;
        $read_data['notice_id'] = $notice_id;

        $rs_read = $m_notice_read->add($read_data);
        if (!$rs_read) {
            $this->error('考试须知失败，请稍后重试或联系管理员', 'mobile/login/index');
        }
    }

    // 获取考试须知数据
    private function getXmnotice() {
        $m_notice = new \app\common\model\Xmsubjectnotice();
        $xm_notice = $m_notice->getOne(['is_deleted' => config('code.status_normal')], 'create_at desc');
        return $xm_notice;
    }

    // 考试须知界面
    public function notice() {
        $notice = $this->getXmnotice();
        $this->assign('notice', $notice);
        return $this->fetch();
    }

    // 正常做题（全部试题）
    public function index()
    {
        // 查看是否已交卷
        $this->isSubDone();

        $read_notice_id = input('get.read_notice_id/d', 0);

        if (!$read_notice_id) {
            // 考试须知
            $this->isReadNotice();
        } else {
            $this->readNotice($read_notice_id);
        }
        
        try {
            $where['s.is_deleted'] = ['EQ', config('code.status_normal')];
            $where['s.cid'] = $this->subject_cid;
            $m_subject = new \app\common\model\Xmsubject();
            $subjects = $m_subject->getAllByPage($where, $this->uid);
            $this->assign(['list' => $subjects]);
            $this->assign('member', array('uid' => $this->uid));
        } catch (\Exception $e) {
            Log::error('subject----->'.$e->getCode().':'.$e->getMessage());
            $this->error('查询失败，请联系管理员', 'Error/error404');
        }

        return $this->fetch();
    }

    // 标注试题
    public function marksubjects()
    {

        // 查看是否已交卷
        $this->isSubDone();
        
        try {
            $where['s.is_deleted'] = ['EQ', config('code.status_normal')];
            $where['s.cid'] = $this->subject_cid;

            $is_page = input('get.page/d', 0);
            if ($is_page >= 1) {
                // 分页进入
                $where['ps.is_marked'] = 1; // 标注过的，用于分页时，不丢失题目
            } else {
                // 非分页链接进入
                $where['ps.is_mark'] = 1; // 标注的
                
                // is_marked 数据校正，将已标注的题目的 is_marked 改为 1
                $rs_all_up = $this->do_is_marked($this->uid, $this->subject_cid);
                if ($rs_all_up === false) {
                    $this->error('查询失败', 'mobile/xmsubject/index');
                }
            }
            $m_subject = new \app\common\model\Xmsubject();
            $page_config = [
                'var_page' => 'page',
                'type'      => 'page\Pagemark',
            ];
            $subjects = $m_subject->getAllByPage($where, $this->uid, $page_config);
            $this->assign(['list' => $subjects]);
            $this->assign('member', array('uid' => $this->uid));
        } catch (\Exception $e) {
            Log::error('subject----->'.$e->getCode().':'.$e->getMessage());
            $this->error('查询失败，请联系管理员', 'Error/error404');
        }

        // 分页无数据跳至默认页
        if (count($subjects) == 0 && $is_page > 0) {
            // unset($subjects);
            $this->redirect('mobile/xmsubject/donesubjects');
        }

        // 标题备注
        $this->assign('title_extra', config('subject.mark_title'));
        
        return $this->fetch('index');
    }

    // 继续试题
    public function undosubjects()
    {

        // 查看是否已交卷
        $this->isSubDone();
        
        try {
            $where['s.is_deleted'] = ['EQ', config('code.status_normal')];
            $where['s.cid'] = $this->subject_cid;
            // $where_query = '(ps.is_doned=0 or ps.is_doned is null) and (ps.is_marked=0 or ps.is_marked is null)'; // 未做的试题

            // is_doned 数据校正，将已做的题目的 is_doned 改为 1
            // $where_query = 'ps.is_doned=0 or ps.is_doned is null'; // 未做的试题

            $is_page = input('get.page/d', 0);
            if ($is_page >= 1) {
                // 分页进入
                $where['ps.is_doned'] = 0; // 已做的，用于分页时，不丢失题目
            } else {
                // 非分页链接进入
                $where['ps.is_done'] = 0; // 已做的，用于分页时，不丢失题目
                // is_doned 数据校正，将已做的题目的 is_doned 改为 1
                $rs_all_up = $this->do_is_doned($this->uid, $this->subject_cid);
                
                if ($rs_all_up === false) {
                    $this->error('查询失败，请联系管理员', 'mobile/xmsubject/index');
                }
            }
            $m_subject = new \app\common\model\Xmsubject();
            $page_config = [
                'var_page' => 'page',
                'type'      => 'page\Pageundo',
            ];

            $m_subject = new \app\common\model\Xmsubject();
            $subjects = $m_subject->getAllByPage($where, $this->uid, $page_config);
            $this->assign(['list' => $subjects]);
            $this->assign('member', array('uid' => $this->uid));
        } catch (\Exception $e) {
            Log::error('subject----->'.$e->getCode().':'.$e->getMessage().':'.$e->getLine());
            $this->error('查询失败，请联系管理员', 'Error/error404');
        }

        $is_page = input('get.page/d', 0);
        if (count($subjects) == 0 && $is_page > 0) {
            // unset($subjects);
            $this->redirect('mobile/xmsubject/undosubjects');
        }

        // 标题备注
        $this->assign('title_extra', config('subject.undo_title'));

        return $this->fetch('index');
    }

    // 已做试题
    public function donesubjects()
    {

        // 查看是否已交卷
        $this->isSubDone();
        
        try {
            $where['s.is_deleted'] = ['EQ', config('code.status_normal')];
            $where['s.cid'] = $this->subject_cid;
            $where['ps.is_done'] = 1;
            $m_subject = new \app\common\model\Xmsubject();
            $page_config = [
                'var_page' => 'page',
                'type'      => 'page\Pagemark',
            ];
            $subjects = $m_subject->getAllByPage($where, $this->uid, $page_config);
            $this->assign(['list' => $subjects]);
            $this->assign('member', array('uid' => $this->uid));
        } catch (\Exception $e) {
            Log::error('subject----->'.$e->getCode().':'.$e->getMessage());
            $this->error('查询失败，请联系管理员', 'Error/error404');
        }

        // 分页无数据跳至默认页
        $is_page = input('get.page/d', 0);
        if (count($subjects) == 0 && $is_page > 0) {
            // unset($subjects);
            $this->redirect('mobile/xmsubject/donesubjects');
        }

        // 标题备注
        $this->assign('title_extra', config('subject.done_title'));

        return $this->fetch('index');
    }

    // 提交试卷
    public function commitafter() {
        // 试卷题目是否做完
        
        $is_open_score = $this->subject_class['is_open_score'];
        if (!empty($is_open_score) && $is_open_score) {
            $m_paper = new \app\common\model\Xmsubpaper();
            $m_paper_whe['cid'] = $this->subject_cid;
            $m_paper_whe['uid'] = $this->uid;
            $xm_paper = $m_paper->getOne($m_paper_whe);

            $this->assign('sub_paper', $xm_paper);
            $this->assign('sub_count', $xm_paper['sub_id'] ? count(json_decode($xm_paper['sub_id'], true)) : 0);
            $this->assign('do_count', $xm_paper['u_answer'] ? count(json_decode($xm_paper['u_answer'], true)) : 0);
            $this->assign('right_count', $xm_paper['right_count'] ? $xm_paper['right_count'] : 0);

            $right_per = $xm_paper['right_pre'] ? $xm_paper['right_pre'] : 0;
            $this->assign('right_percent', $right_per);
            
            $this->assign('is_open_score', $is_open_score);
        } else {
            $this->assign('is_open_score', 0);
        }
        return $this->fetch();
    }

    // 清除session，并跳至登录
    public function tologin() {
        Session::delete('member');
        $this->redirect('mobile/login/index');
    }

    // 标注数据全局更新
    private function do_is_marked($uid, $cid) {
        $rs_up = Db::execute("update xm_subject_paper_single set is_marked=is_mark where uid=:uid and cid=:cid",['uid'=>$uid,'cid'=>$cid]);
        return $rs_up;
    }

    // 已做试题数据全局更新
    private function do_is_doned($uid, $cid) {
        $rs_up = Db::execute("update xm_subject_paper_single set is_doned=is_done where uid=:uid and cid=:cid",['uid'=>$uid,'cid'=>$cid]);
        return $rs_up;
    }

}
