<?php
namespace banmi;
use think\facade\Db; 

class QCloud
{
    
    
    /**
 * 删除图片
 * @param $key
 */
 function deleteImageToCos($key,$settings)
{ 
           
    #获取基本配置
    $secretId = $settings['tx_secretId'];                     //"云 API 密钥 SecretId";
    $secretKey =$settings['tx_secretKey'];                    //"云 API 密钥 SecretKey";
    $host =$settings['tx_bucket'].'.cos.'. $settings['tx_region'].'.myqcloud.com';
    #组合请求路由
    $path = "/" . $key;
    #组合请求连接
    $url = $host . $path;
  
    #获取签名
    $authorization = $this->requestSign("delete", $path, $host, $secretKey, $secretId);
    #组合头部
    $header = array(
        'Authorization: ' . $authorization,
        'Date: ' . gmdate('D, d M Y H:i:s T')
    );
    #请求
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
    $data = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);
 
}
    
    /**
 * 请求获取签名
 * @param $method
 * @param $path
 * @param $bucketURL
 * @param $secretKey
 * @param $secretId
 * @return string
 */
 function requestSign($method, $path, $bucketURL, $secretKey, $secretId)
{
    $signTime = (string)(time() - 60) . ';' . (string)(time() + 1200);
    $host = parse_url($bucketURL);
    
    $httpString = sprintf("%s\n%s\n\nhost=%s\n", strtolower($method), $path, $host['path']);
    $stringToSign = sprintf("sha1\n%s\n%s\n", $signTime, sha1($httpString));
    $signKey = hash_hmac('sha1', $signTime, $secretKey);
    $signature = hash_hmac('sha1', $stringToSign, $signKey);
    return sprintf('q-sign-algorithm=sha1&q-ak=%s&q-sign-time=%s&q-key-time=%s&q-header-list=host&q-url-param-list=&q-signature=%s', $secretId, $signTime, $signTime, $signature);
}
	// 获取临时密钥
	function getTempKeys($config) {
				$result = null;
		try{
			if(array_key_exists('policy', $config)){
				$policy = $config['policy'];
			}else{
				if(array_key_exists('bucket', $config)){
					$ShortBucketName = substr($config['bucket'],0, strripos($config['bucket'], '-'));
					$AppId = substr($config['bucket'], 1 + strripos($config['bucket'], '-'));
				}else{
					throw new Exception("bucket== null");
				}
				if(array_key_exists('allowPrefix', $config)){
					if(!(strpos($config['allowPrefix'], '/') === 0)){
					$config['allowPrefix'] = '/' . $config['allowPrefix'];
					}
				}else{
					throw new Exception("allowPrefix == null");
				}
				$policy = array(
					'version'=> '2.0',
					'statement'=> array(
						array(
							'action'=> $config['allowActions'],
							'effect'=> 'allow',
							'principal'=> array('qcs'=> array('*')),
							'resource'=> array(
								'qcs::cos:' . $config['region'] . ':uid/' . $AppId . ':' . $config['bucket'] . $config['allowPrefix']
							)
						)
					)
				);	
			}
			$policyStr = str_replace('\\/', '/', json_encode($policy));
			$Action = 'GetFederationToken';
			$Nonce = rand(10000, 20000);
			$Timestamp = time();
			$Method = 'POST';
			if(array_key_exists('durationSeconds', $config)){
				if(!(is_integer($config['durationSeconds']))){
					throw new exception("durationSeconds must be a int type");
				}
			}
			$params = array(
				'SecretId'=> $config['secretId'],
				'Timestamp'=> $Timestamp,
				'Nonce'=> $Nonce,
				'Action'=> $Action,
				'DurationSeconds'=> $config['durationSeconds'],
				'Version'=>'2018-08-13',
				'Name'=> 'cos',
				'Region'=> $config['region'],
				'Policy'=> urlencode($policyStr)
			);
			$params['Signature'] = $this->getSignature($params, $config['secretKey'], $Method, $config);
			$url = $config['url'];
			$ch = curl_init($url);
			if(array_key_exists('proxy', $config)){
				$config['proxy'] && curl_setopt($ch, CURLOPT_PROXY, $config['proxy']);
			}
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
			curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->json2str($params));
			$result = curl_exec($ch);
			if(curl_errno($ch)) $result = curl_error($ch);
			curl_close($ch);
			$result = json_decode($result, 1);
			if (isset($result['Response'])) {
				$result = $result['Response'];
				if(isset($result['Error'])){
					return 0;
				}
				$result['startTime'] = $result['ExpiredTime'] - $config['durationSeconds'];
			}
			$result = $this->backwardCompat($result);
			return $result;
		}catch(Exception $e){
			if($result == null){
				$result = "error: " . + $e->getMessage();
			}else{
				$result = json_encode($result);
			}
			throw new Exception($result);
		}
	}
 
   	// 计算临时密钥用的签名
	function getSignature($opt, $key, $method, $config) {
		$formatString = $method . $config['domain'] . '/?' . $this->json2str($opt, 1);
		$sign = hash_hmac('sha1', $formatString, $key);
		$sign = base64_encode($this->_hex2bin($sign));
		return $sign;
	}
		function json2str($obj, $notEncode = false) {
		ksort($obj);
		$arr = array();
		if(!is_array($obj)){
			throw new Exception($obj + " must be a array");
		}
		foreach ($obj as $key => $val) {
			array_push($arr, $key . '=' . ($notEncode ? $val : rawurlencode($val)));
		}
		return join('&', $arr);
	}
	function _hex2bin($data) {
		$len = strlen($data);
		return pack("H" . $len, $data);
	}
    	function backwardCompat($result) {
		if(!is_array($result)){
			throw new Exception($result + " must be a array");
		}
		$compat = array();
		foreach ($result as $key => $value) {
			if(is_array($value)) {
				$compat[lcfirst($key)] = $this->backwardCompat($value);
			} elseif ($key == 'Token') {
				$compat['sessionToken'] = $value;
			} else {
				$compat[lcfirst($key)] = $value;
			}
		}
		return $compat;
	}
}
