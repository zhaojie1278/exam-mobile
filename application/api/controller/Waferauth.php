<?php
namespace app\api\controller;

/**
 * 微信权限相关信息:openid等
 */
class Waferauth extends Common {

    /**
     * 获取OPENID信息
     */
    public function getopenid() {
        // https://api.weixin.qq.com/sns/jscode2session?appid=APPID&secret=SECRET&js_code=JSCODE&grant_type=authorization_code
        $data = input('post.');
        // $data['code'] = 'asdasdaasdadssaddsa';
        if (empty($data)) {
            return show(config('code.error'), 'fail', [], 400);
        }
        $js_code = $data['code'];
        $query = [
            'appid' => config('wafer_config.appid'),
            'secret' => config('wafer_config.appsecret'),
            'js_code' => $js_code,
            'grant_type' => config('wafer_config.grant_type')
        ];
        $wxurl = config('wafer_config.wxurl');
        $wxurl .= http_build_query($query);
        // halt($wxurl);
        $wxres =  file_get_contents($wxurl);
        $wxresjson = json_decode($wxres, true); //对JSON格式的字符串进行编码
        // halt($wxresjson);
        if (empty($wxresjson) || empty($wxresjson['openid'])) {
            return show(config('code.error'), $wxresjson['errmsg'], [], 400);
        }
        // $wxres = get_object_vars($jsondecode);//转换成数组
        $openid = $wxresjson['openid'];
        // halt($wxres);
        $data = ['openid' => $openid]; // todo
        return show(config('code.success'), 'ok', $data, 200);//输出openid
    }
}