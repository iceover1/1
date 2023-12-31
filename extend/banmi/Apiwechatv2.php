<?php
namespace banmi;
use think\facade\Db; 
class Apiwechatv2
{
        //微信小程序支付v2接口 
    public function weixinapp($appid, $openid, $mch_id, $key, $out_trade_no, $body, $total_fee,$notify) {
        //统一下单接口
        $unifiedorder = $this->unifiedorder($appid, $openid, $mch_id, $key, $out_trade_no, $body, $total_fee,$notify);
 
        $parameters = array(
            'appId' => $appid, //小程序ID
            'timeStamp' => '' . time() . '', //时间戳
            'nonceStr' => $this->createNoncestr(), //随机串
            'package' => 'prepay_id=' . $unifiedorder['prepay_id'], //数据包
            'signType' => 'MD5'//签名方式
        );
        //签名
        $parameters['paySign'] = $this->getSign($parameters,$key);
        return $parameters;
    }
      //统一下单接口
    private function unifiedorder($appid, $openid, $mch_id, $key, $out_trade_no, $body, $total_fee,$notify) {
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $parameters = array(
            'appid' => $appid, //小程序ID
            'mch_id' => $mch_id, //商户号
            'nonce_str' => $this->createNoncestr(), //随机字符串
//            'body' => 'test', //商品描述
            'body' =>$body,
//            'out_trade_no' => '2018013106125348', //商户订单号
            'out_trade_no'=> $out_trade_no,
//            'total_fee' => floatval(0.01 * 100), //总金额 单位 分
            'total_fee' => $total_fee,
           'spbill_create_ip' => $_SERVER['REMOTE_ADDR'], //终端IP
            // 'spbill_create_ip' => '192.168.0.161', //终端IP
            'notify_url' =>  $notify, //通知地址  确保外网能正常访问
            'openid' => $openid, //用户id
            'trade_type' => 'JSAPI'//交易类型
        );
        //统一下单签名
        $parameters['sign'] = $this->getSign($parameters,$key);
        $xmlData = $this->arrayToXml($parameters);
        $return = $this->xmlToArray($this->postXmlCurl($xmlData, $url, 60));
        return $return;
    }
    private static function postXmlCurl($xml, $url, $second = 30) 
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        set_time_limit(0);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WxPayException("curl出错，错误码:$error");
        }
    }
    //数组转换成xml
    public function arrayToXml($arr) {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $xml .= "<" . $key . ">" . arrayToXml($val) . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
    //xml转换成数组
    public function xmlToArray($xml) {
        //禁止引用外部xml实体 
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);
        return $val;
    }
    //作用：产生随机字符串，不长于32位
    private function createNoncestr($length = 32) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) { 
                $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        } 
        return $str; 
     }
    //作用：生成签名 
     private function getSign($Obj,$key) { 
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $key;
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }
    ///作用：格式化参数，签名过程需要使用
    private function formatBizQueryParaMap($paraMap, $urlencode) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    
    
}
