<?php
namespace app\api\controller;

use think\Log;
use think\Session;
use think\Loader;
use think\Db;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use PHPExcel_Writer_Excel5;
use PHPExcel_Writer_Excel2007;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
            return show_arr(config('code.error'), '您已提交试卷，不可重新做题或交卷', ['rs_subdone_url' => url('mobile/xmsubject/commitafter')], 200);
        } else {
            return show_arr(config('code.success'));
        }
    }

    // 做题
    public function dosub() {

        // 已交卷判定
        $rs_isdone = $this->isSubDone();
        if (config('code.error') == $rs_isdone['status']) {
            return json($rs_isdone, $rs_isdone['httpcode']);
        }
        $data = input('post.');
        try {
            $sub_id = $data['sub_id'] . '';
            $answer = $data['answer'] . '';
            $m_xm_subject = new \app\common\model\Xmsubject();
            $xm_sub_whe = ['id' => $sub_id];
            $xm_subject = $m_xm_subject->getOne($xm_sub_whe);

            $is_right = $xm_subject['check_answer'] == $answer ? 1 : 0;
            $m_paper_single = new \app\common\model\Xmsubpapersingle();
            $old_pap_whe = ['sub_id' => $sub_id, 'uid' => $this->uid];
            $m_old_paper = $m_paper_single->getOne($old_pap_whe);
            $rs = false;
            if ($m_old_paper) {
                $edit_data = array(
                    'id' => $m_old_paper['id'],
                    's_answer' => $xm_subject['check_answer'],
                    'score' => $xm_subject['score'],
                    'do_time' => time(),
                    'is_done' => 1,
                    'u_answer' => $answer,
                    'is_right' => $is_right,
                    // 'is_doned' => 1
                );
                $rs = $m_paper_single->edit($edit_data);
            } else {
                $add_data = array(
                    'sub_id' => $sub_id,
                    's_answer' => $xm_subject['check_answer'],
                    'uid' => $this->uid,
                    'score' => $xm_subject['score'],
                    'do_time' => time(),
                    'is_done' => 1,
                    'u_answer' => $answer,
                    'cid' => $xm_subject['cid'],
                    'is_right' => $is_right,
                    // 'is_doned' => 1
                );
                $rs = $m_paper_single->add($add_data);
            }
            if ($rs === false) {
                Log::error('------->do_sub add err: sub_id - '.$sub_id);
                return show(config('code.error'), '提交失败D-C001，请稍后重试或联系管理员', [], 200);
            }
            $rs_data = ['u_id' => $this->uid, 'do_rs' => $rs];
        } catch(\Exception $e) {
            Log::error('------->do_sub error:'.$e->getMessage());
            return show(config('code.error'), '系统异常，请联系管理员或稍后重试', [], 500);
        }
        return show(config('code.success'), 'OK', $rs_data, 200);
    }

    
    // 标注
    public function domark() {

        // 已交卷判定
        $rs_isdone = $this->isSubDone();
        if (config('code.error') == $rs_isdone['status']) {
            return json($rs_isdone, $rs_isdone['httpcode']);
        }

        $data = input('post.');
        try {
            $sub_id = $data['sub_id'] . '';
            $is_mark = $data['is_mark'] + 0;
            $m_xm_subject = new \app\common\model\Xmsubject();
            $xm_sub_whe = ['id' => $sub_id];
            $xm_subject = $m_xm_subject->getOne($xm_sub_whe);

            
            $m_paper_single = new \app\common\model\Xmsubpapersingle();
            $old_pap_whe = ['sub_id' => $sub_id, 'uid' => $this->uid];
            $m_old_paper = $m_paper_single->getOne($old_pap_whe);
            $rs = false;
            if ($m_old_paper) {
                $edit_data = array(
                    'id' => $m_old_paper['id'],
                    'is_mark' => $is_mark ? 0 : 1,
                    'mark_time' => time(),
                    // 'is_marked' => 1
                );
                $rs = $m_paper_single->edit($edit_data);
            } else {
                $is_right = $xm_subject['check_answer'] == '' ? 1 : 0;
                $add_data = array(
                    'sub_id' => $sub_id,
                    'uid' => $this->uid,
                    'cid' => $xm_subject['cid'],
                    'is_mark' => $is_mark ? 0 : 1,
                    'mark_time' => time(),
                    's_answer' => $xm_subject['check_answer'],
                    'score' => $xm_subject['score'],
                    'is_right' => $is_right,
                    // 'is_marked' => 1
                );
                $rs = $m_paper_single->add($add_data);
            }
            if ($rs === false) {
                return show(config('code.error'), '标注失败，请稍后重试或联系管理员', [], 500);
            }
            $rs_data = ['u_id' => $this->uid, 'do_rs' => $rs];
        } catch(\Exception $e) {
            Log::error('------->domark err: sub_id - '.$sub_id . '--e:'.$e->getMessage());
            return show(config('code.error'), '系统异常，请联系管理员或稍后重试'.$e->getLine(), [], 500);
        }
        return show(config('code.success'), 'OK', $rs_data, 200);
    }

    // 提交试卷前判定是否做完
    public function dosubcommitbefore () {

        // 已交卷判定
        $rs_isdone = $this->isSubDone();
        if (config('code.error') == $rs_isdone['status']) {
            return json($rs_isdone, $rs_isdone['httpcode']);
        }
        
        try {
            // 已做题目
            $m_single = new \app\common\model\Xmsubpapersingle();
            $m_paper_sg_whe['cid'] = $this->subject_cid;
            $m_paper_sg_whe['uid'] = $this->uid;
            $m_paper_sg_whe['u_answer'] = ['NEQ', ''];
            $do_subs = $m_single->getAll($m_paper_sg_whe);
            $do_sub_count = count($do_subs) + 0;
            
            // 总试题数量
            $m_sub = new \app\common\model\Xmsubject();
            $sub_where = ['cid' => $this->subject_cid];
            $sub_count = $m_sub->getCountByCondition($sub_where) + 0;

            Log::record('do_sub_count:'.$do_sub_count.'; sub_count:'.$sub_count);

            $undo_count = $sub_count - $do_sub_count;

            if ($do_sub_count != $sub_count) {
                return show(config('code.error'), '您还剩余 '.$undo_count.' 题未做，您确认交卷吗？', ['isprompt' => 1], 200);
            }
        } catch(\Exception $e) {
            Log::error('------->dosubmitcommit before error:'.$e->getMessage());
            return show(config('code.error'), '系统异常，请联系管理员或稍后重试', [], 500);
        }
        return show(config('code.success'), 'OK');
    }
    
    // 提交试卷
    public function dosubcommit () {

        // 已交卷判定
        $rs_isdone = $this->isSubDone();
        if (config('code.error') == $rs_isdone['status']) {
            return json($rs_isdone, $rs_isdone['httpcode']);
        }
        
        try {
            // 已做试题
            $m_single = new \app\common\model\Xmsubpapersingle();
            $m_paper_sg_whe['cid'] = $this->subject_cid;
            $m_paper_sg_whe['uid'] = $this->uid;
            $do_subs = $m_single->getAll($m_paper_sg_whe);

            // 总试题数量
            $m_sub = new \app\common\model\Xmsubject();
            $sub_where = ['cid' => $this->subject_cid, 'is_deleted' => config('code.status_normal')];

            $columns = 'id,check_answer';
            $sub_id_answers = $m_sub->getAllColumns($sub_where, $columns);

            $analy_rs = $m_single->analyPaperSingles($do_subs);
            $m_paper = new \app\common\model\Xmsubpaper();

            // 考试花费时间
            $do_cost_time = 0;
            $subc_begin = $this->subject_class['begin_time'];
            $subc_end = $this->subject_class['end_time'];
            $do_subc_time = 0;
            $now_time = time();


            Log::record('$do_subc_time::'.$do_subc_time);
            Log::record('$subc_end::'.$subc_end);
            Log::record('$subc_begin::'.$subc_begin);
            Log::record('$now_time::'.$now_time); 

            if (input('post.auto/d')) {
                Log::record('----------is auto commit:'.var_export(input('post.auto/d'), true));
            } else {
                Log::record('----------is not auto commit:'.var_export(input('post.auto/d'), true));
            }

            // 最后一次阅读当前考卷须知的时间
            $m_notice_read = new \app\common\model\Xmsubjectnoticeread();
            $notice_whe = [];
            $notice_whe['subject_class_id'] = $this->subject_cid;
            $notice_whe['uid'] = $this->uid;
            $notice_whe['is_read'] = 1;
            $last_notice_read = $m_notice_read->getOne($notice_whe, 'id desc');
            $read_time = $last_notice_read['create_at'] ? strtotime($last_notice_read['create_at']) : 0;

            if ($subc_end != 0 && $subc_begin != 0) {
                if ($now_time <= $subc_end) {
                    $do_subc_end = $now_time;
                } else {
                    $do_subc_end = $subc_end;
                }

                // 考试须知阅读时间大于考试时间
                if ($subc_begin <= $read_time) {
                    $subc_begin = $read_time;
                }
                $do_subc_time = $do_subc_end - $subc_begin;
            }

            $paper_data = array(
                'sub_id' =>  $sub_id_answers ? json_encode(array_keys($sub_id_answers)) : null,
                's_answer' => $sub_id_answers ? json_encode($sub_id_answers) : null,
                'uid' => $this->uid,
                'time' => $do_subc_time,
                'u_answer' => $analy_rs['u_answers'],
                'score' => $analy_rs['sum_score'],
                'cid' => $this->subject_cid,
                'right_count' => $analy_rs['right_count'],
                'right_pre' => round($analy_rs['right_count'] / count($sub_id_answers), 2) * 100,
            );

            $rs = $m_paper->add($paper_data);
            if (!$rs) {
                return show(config('code.error'), '提交失败，请稍后重试或联系管理员', [], 500);
            }
            $rs_data = ['u_id' => $this->uid, 'do_rs' => $rs, 'a_href' => url('mobile/xmsubject/commitafter')];
        } catch(\Exception $e) {
            Log::error('------->dosubcommit err: subject_cid - '.$this->subject_cid.'--e:'.$e->getMessage());
            return show(config('code.error'), '系统异常，请联系管理员或稍后重试'.$e->getMessage(), [], 500);
        }
        return show(config('code.success'), 'OK', $rs_data, 200);
    }

    // 生成考试结果表格
    public function phpexcelGenerate($stu_info, $xm_paper_singles) {
        // 试题模型
        $m_xm_sub = new \app\common\model\Xmsubject();

        //2.实例化PHPExcel类
        Loader::import('PHPExcel.PHPExcel');
        Loader::import('PHPExcel.PHPExcel.PHPExcel_IOFactory');
        Loader::import('PHPExcel.PHPExcel.PHPExcel_Cell');
        // $objPHPExcel = vendor('PHPExcel');
        // $objPHPExcel = vendor('PHPExcel_IOFactory');
        
        $objPHPExcel = new \PHPExcel();

        // 考试统计信息 -- sheet 1 -- begin

        //3.激活当前的sheet表
        $sheet1 = 0;
        $objPHPExcel->setActiveSheetIndex($sheet1);
        //4.设置表格头（即excel表格的第一行）
        $objPHPExcel->setActiveSheetIndex($sheet1)
                ->setCellValue('A1', '题干')
                ->setCellValue('B1', '题目')
                ->setCellValue('C1', '正确选项')
                ->setCellValue('D1', '错误选项');

        // 设置换行
        $objPHPExcel->setActiveSheetIndex($sheet1)->getStyle('A')->getAlignment()->setWrapText(TRUE);
        $objPHPExcel->setActiveSheetIndex($sheet1)->getStyle('B')->getAlignment()->setWrapText(TRUE);
        $objPHPExcel->setActiveSheetIndex($sheet1)->getStyle('C')->getAlignment()->setWrapText(TRUE);
        $objPHPExcel->setActiveSheetIndex($sheet1)->getStyle('D')->getAlignment()->setWrapText(TRUE);

        //设置水平居中、垂直居中
        $objPHPExcel->setActiveSheetIndex($sheet1)->getStyle('A')->getAlignment()
        ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$objPHPExcel->setActiveSheetIndex($sheet1)->getStyle('A')->getAlignment()
        ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_TOP);
        $objPHPExcel->setActiveSheetIndex($sheet1)->getStyle('B')->getAlignment()
        ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$objPHPExcel->setActiveSheetIndex($sheet1)->getStyle('B')->getAlignment()
        ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_TOP);
        $objPHPExcel->setActiveSheetIndex($sheet1)->getStyle('C')->getAlignment()
        ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$objPHPExcel->setActiveSheetIndex($sheet1)->getStyle('C')->getAlignment()
        ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_TOP);
        $objPHPExcel->setActiveSheetIndex($sheet1)->getStyle('D')->getAlignment()
        ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$objPHPExcel->setActiveSheetIndex($sheet1)->getStyle('D')->getAlignment()
        ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_TOP);

        //设置单元格宽度
        $objPHPExcel->setActiveSheetIndex($sheet1)->getColumnDimension('A')->setWidth(60);
        $objPHPExcel->setActiveSheetIndex($sheet1)->getColumnDimension('B')->setWidth(60);
        $objPHPExcel->setActiveSheetIndex($sheet1)->getColumnDimension('C')->setWidth(35);
        $objPHPExcel->setActiveSheetIndex($sheet1)->getColumnDimension('D')->setWidth(35);


        //5.循环刚取出来的数组，将数据逐一添加到excel表格。
        $xm_paper_count = count($xm_paper_singles);
        for($i=0;$i<$xm_paper_count;$i++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$xm_paper_singles[$i]['sub_stem']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),$xm_paper_singles[$i]['sub_order_no'].'.'.$xm_paper_singles[$i]['question']);

            // 正确选项
            $s_answer_txt = $m_xm_sub->getSubOption($xm_paper_singles[$i]['answer'], $xm_paper_singles[$i]['s_answer']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+2),  $xm_paper_singles[$i]['s_answer'].'.'.$s_answer_txt);

            // 错误选项
            $u_answer_txt = $m_xm_sub->getSubOption($xm_paper_singles[$i]['answer'], $xm_paper_singles[$i]['u_answer']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+2), $xm_paper_singles[$i]['u_answer'].'.'.$u_answer_txt);
        }

        //7.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('个人考试错题记录');
        // -- sheet 1 -- end

        return $objPHPExcel;
    }

    // 邮件发送考试结果
    public function exporttomail() {
        // 查看错题
        $m_xm_member = new \app\common\model\Xmmember();
        $xm_sub_whe = [
            'id' => $this->uid,
        ];
        $stu_info = $m_xm_member->getOne($xm_sub_whe);
        $m_paper = new \app\common\model\Xmsubpapersingle();
        $m_paper_s_whe['p.cid'] = $this->subject_cid;
        $m_paper_s_whe['p.uid'] = $this->uid;
        $m_paper_s_whe['p.is_right'] = 0; // 答题错误
        $m_paper_s_whe['p.u_answer'] = ['NEQ', ''];
        $xm_paper_singles = $m_paper->getAllDoSubs($m_paper_s_whe);
        if (empty($xm_paper_singles) || count($xm_paper_singles) == 0) {
            return show(config('code.error'), '抱歉，当前无考试记录', [], 200);
        }

        if (empty($stu_info['mail'])) {
            return show(config('code.error'), '抱歉，当前学号无邮箱信息，请完善邮箱后再发送', [], 200);
        }

        try {

            $objPHPExcel = $this->phpexcelGenerate($stu_info, $xm_paper_singles);

            $subject_class_name = $stu_info['real_name'].'-'.$this->subject_class['name'];
            //6.设置保存的Excel表格名称
            $filename = $subject_class_name.'-错题-'.time().'.xls';
            Log::record("send-mail-filename::".$filename);
            Log::record("ROOT_PATH::".ROOT_PATH);

            // $filename_gbk = iconv("utf-8", "gb2312", $filename);
            // Log::error("send-mail-rs2::".$filename_gbk);

            // ob_end_clean();//清除缓冲区,避免乱码

            $file_path = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'temp' . DS . $filename;
            //生成excel文件
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save($file_path);

            $wafer_path = ROOT_PATH . 'public' . DS . 'static' . DS . 'mobile' . DS . 'images' . DS . 'wafer-1.jpg';
            $attachments = array($file_path, $wafer_path);
            $body_content = '你好，'.$stu_info['real_name'].'，本次考试<'.$this->subject_class['name'].'>错题见附件。';
            $rs = $this->sendMail($stu_info['mail'], '考试错题记录'.date('Ymd', time()), $body_content, $attachments);
            if (!$rs) {
                return show(config('code.error'), '邮件发送失败，请联系管理员或稍后重试', [], 200);
            }
            Log::record("send-mail-rs::".var_export($rs, true));
            // sleep(5);
            if (file_exists($file_path)) {
                $rs_unlink = @unlink($file_path);
                Log::record('file delete rs:'.var_export($rs_unlink, true));
            }
            return show(config('code.success'), 'OK', ['mail' => hide_star($stu_info['mail'])], 200);
        } catch(\Exception $e) {
            Log::error("error--- exporttomail ::".$e->getMessage());
            return show(config('code.error'), '系统异常，请联系管理员或稍后重试', [], 500);
        }
    }


    // 发送邮件
    public function sendMail($to, $title, $body_content, $attachments) {

        $rs_send = false;
        // Instantiation and passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = config('app.email_host');                    // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = config('app.email_account');                     // SMTP username
            $mail->Password   = config('app.email_authcode');                               // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 465;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            //Recipients
            $mail->setFrom(config('app.email_account'), config('app.email_fromer'));
            $mail->addAddress($to);     // Add a recipient
            // $mail->addAddress('ellen@example.com');               // Name is optional
            // $mail->addReplyTo('info@example.com', 'Information');
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            // Attachments
            if ($attachments) {
                foreach($attachments as $attach) {
                    $mail->addAttachment($attach);         // Add attachments
                }
            }
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $title;
            $mail->Body    = $body_content;
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $rs_send = $mail->send();
            // echo 'Message has been sent';
        } catch (Exception $e) {
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            Log::error('mail error---------------->'.$e->getMessage());
        }
        return $rs_send;
    }
}
