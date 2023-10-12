<?php
namespace banmi;
use think\facade\Db; 
class Apimpweixinv2
{
        //微信公众号支付v2接口 
// private $config=[
//     'appId'=>'<公众号APPID>', 
//     'mch_id'=>'<微信商户号ID>',
//     'key'=>'<微信商户KEY>',
//     'secret'=>'<微信商户SECRET>'
//   ];
//   $desc="描述";
    // $openid=input('openid');
  public function wxpay($appId,$mch_id,$key,$secret,$desc,$openid,$out_trade_no,$total_amount,$notify_url){
 
 
      header('Content-Type: text/html; charset=utf-8');
 
      $data=array(
      'appid'=> $appId,//公众号APPID
      'mch_id'=> $mch_id,//微信商户号ID
      'nonce_str'=> $this->createNoncestr(),//随机字符串
      'out_trade_no'=> $out_trade_no,//商户订单号
      'openid'=> $openid,//用户openid
      'total_fee'=>$total_amount,//金额
      'body'=> $desc,//充值描述信息
      'spbill_create_ip'=> $_SERVER["REMOTE_ADDR"],//Ip地址
      'trade_type'=>'JSAPI',
      'notify_url'=>$notify_url//你的回调地址
    );
      $secrect_key = $key;//API密码
      $data = array_filter($data);//过滤函数
      ksort($data);
      $str ='';
      foreach($data as $k=>$v) {
        $str.=$k.'='.$v.'&';
      }
      $str.='key='.$secrect_key;//把秘钥和字符串拼接起来
      $data['sign'] = strtoupper(md5($str));//用md5得到sign 转换成大写
      $xml = $this->arraytoxml($data);//拼接成xml的格式
      $url='https://api.mch.weixin.qq.com/pay/unifiedorder'; //调用接口
      $res = $this->wx_curl($xml,$url);
      $return = $this->xmltoarray($res);
      $responseObj = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
      $jsonStr = json_encode($responseObj);//转换为字符串
      $jsonArray = json_decode($jsonStr,true);//转换为数组
      if (empty($jsonArray['nonce_str'] ) ) {
     
         return  json_encode($jsonArray,JSON_UNESCAPED_UNICODE);
          die;
      } 
      
      
      if($jsonArray['return_code']='SUCCESS' && $jsonArray['result_code']='SUCCESS'){
        $time = time();//时间戳
        $info = array(
          'appId'=>$appId,
          'nonceStr'=>$jsonArray['nonce_str'],
          'package'=>'prepay_id='.$jsonArray['prepay_id'],
          'signType'=>'MD5',
          'timeStamp'=>"".$time."",
        );
      $info_s = array_filter($info);//过滤函数
      ksort($info_s);
      $str_s ='';
      foreach($info as $k=>$v) {
        $str_s.=$k.'='.$v.'&';
      }
      $str_s.='key='.$key;//把秘钥和字符串拼接起来
      $info['sign']=strtoupper(md5($str_s));
      $infos = array(
        'status'=>1,
        'content'=>'success',
        'result'=>$info
      );
      return json($info);     
    }else{
      return json([
        'status'=>0,
        'content'=>$jsonArray['return_msg'],
        'result'=>$jsonArray['err_code_des']
      ]);
    }
  }
   

  public function createNoncestr($length =32){
    $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
    $str ="";
    for ( $i = 0; $i < $length; $i++ ) {
    $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
    }
    return $str;
  }
  public function arraytoxml($data){
    $str='<xml>';
    foreach($data as $k=>$v) {
        $str.='<'.$k.'>'.$v.'</'.$k.'>';
    }
    $str.='</xml>';
    return $str;
  }
  public function xmltoarray($xml) {
    $php = phpversion();
    
    if ( $php  < '8.0.0') {
     libxml_disable_entity_loader(true);//禁止引用外部xml
}

 

    $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    $val = json_decode(json_encode($xml),true);
    return $val;
  }
  public function wx_curl($vars,$url,$second = 30, $aHeader = array()) {
    $isdir =  "../extend/cert/";//证书位置
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_TIMEOUT, $second);//设置执行最长秒数
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);// 终止从服务端进行验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//
    curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');//证书类型
    curl_setopt($ch, CURLOPT_SSLCERT, $isdir . 'apiclient_cert.pem');//证书位置
    curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');//CURLOPT_SSLKEY中规定的私钥的加密类型
    curl_setopt($ch, CURLOPT_SSLKEY, $isdir . 'apiclient_key.pem');//证书位置
    if (count($aHeader) >= 1) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);//设置头部
    }
    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);//全部数据使用HTTP协议中的"POST"操作来发送
 
    $data = curl_exec($ch);//执行回话
    if ($data) {
        curl_close($ch);
        return $data;
    } else {
        $error = curl_errno($ch);
        echo "call faild, errorCode:$error\n";
        curl_close($ch);
        return false;
    }
  }


    
    
}
