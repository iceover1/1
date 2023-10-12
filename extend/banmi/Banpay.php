<?php
namespace banmi;
use think\facade\Db;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Contract\HttpClientInterface;
class Banpay {
	//微信转账 零钱到微信
	function weixin_v3_transfer($data) {
		$config = [
		                'wechat' => [
		                    'default' => [
		                        // 必填-商户号，服务商模式下为服务商商户号
		// 可在 https://pay.weixin.qq.com/ 账户中心->商户信息 查看
		'mch_id' => $data['mch_id'],
		                        // 必填-商户秘钥
		// 即 API v3 密钥(32字节，形如md5值)，可在 账户中心->API安全 中设置
		'mch_secret_key' => $data['mch_secret_key'], 
		                        'mch_secret_cert' => $data['mch_secret_cert'], 
		                        'mch_public_cert_path' => $data['mch_public_cert_path'], 
		                        'mini_app_id' => $data['mini_app_id'],
		                        'app_id' => $data['mini_app_id'],
		                        // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
		'mode' => Pay::MODE_NORMAL,
		                    ]
		                ],
		            ];
		Pay::config($config);
		$order = [
		    'appid' => $data['mini_app_id'], 
		    'out_batch_no' => $data['out_detail_no'],
		    'batch_name' => '转账',
		    'batch_remark' => '转账',
		    'total_amount' => $data['transfer_amount'],
		    'total_num' => 1,
		    'transfer_detail_list' => [
		        [
		            'out_detail_no' =>$data['out_detail_no'],
		            'transfer_amount' => $data['transfer_amount'],
		            'transfer_remark' => '转账',
		            'openid' => $data['openid'],
		            // 'user_name' => '闫嵩达'  // 明文传参即可，sdk 会自动加密
		],
		    ],
		];
		$result = Pay::wechat()->transfer($order);
		return $result;
	}
	//微信小程序付款
	function weixin_wechat_v3($data) {
		$config = [
		                'wechat' => [
		                    'default' => [
		                        // 必填-商户号，服务商模式下为服务商商户号
		// 可在 https://pay.weixin.qq.com/ 账户中心->商户信息 查看
		'mch_id' => $data['mch_id'],
		                        // 必填-商户秘钥
		// 即 API v3 密钥(32字节，形如md5值)，可在 账户中心->API安全 中设置
		'mch_secret_key' => $data['mch_secret_key'],
		                        // 必填-商户私钥 字符串或路径
		// 即 API证书 PRIVATE KEY，可在 账户中心->API安全->申请API证书 里获得
		// 文件名形如：apiclient_key.pem
		'mch_secret_cert' => $data['mch_secret_cert'],
		                        // 必填-商户公钥证书路径
		// 即 API证书 CERTIFICATE，可在 账户中心->API安全->申请API证书 里获得
		// 文件名形如：apiclient_cert.pem
		'mch_public_cert_path' => $data['mch_public_cert_path'],
		                        // 必填-微信回调url
		// 不能有参数，如?号，空格等，否则会无法正确回调
		'notify_url' => $data['notify_url'],
		                        // 选填-公众号 的 app_id
		// 可在 mp.weixin.qq.com 设置与开发->基本配置->开发者ID(AppID) 查看
		'mp_app_id' => '',
		                        // 选填-小程序 的 app_id
		'mini_app_id' => $data['mini_app_id'],
		                        'wechat_public_cert_path' => [
		                            '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.
		                            '/Cert/wechatPublicKey.crt',
		                        ],
		                        // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
		'mode' => Pay::MODE_NORMAL,
		                    ]
		                ],
		            ];
		Pay::config(array_merge($config, ['_force' => true]) );
		$order = [
		                'out_trade_no' => $data['out_trade_no'],
		                'description' => $data['description'],
		                'amount' => [
		                    'total' => $data['total'],
		                    'currency' => 'CNY',
		                ],
		                'payer' => [
		                    'openid' => $data['openid'],
		                ]
		            ];
		$result = Pay::wechat()->mini($order);
		return $result;
	}
    //微信app付款
	function app_weixin_wechat_v3($data) {
	    
	     
		$config = [
	    'wechat' => [
	    'default' => [
		                        // 必填-商户号，服务商模式下为服务商商户号
		// 可在 https://pay.weixin.qq.com/ 账户中心->商户信息 查看
		'mch_id' => $data['mch_id'],
		                        // 必填-商户秘钥
		// 即 API v3 密钥(32字节，形如md5值)，可在 账户中心->API安全 中设置
		'mch_secret_key' => $data['mch_secret_key'],
		                        // 必填-商户私钥 字符串或路径
		// 即 API证书 PRIVATE KEY，可在 账户中心->API安全->申请API证书 里获得
		// 文件名形如：apiclient_key.pem
		'mch_secret_cert' => $data['mch_secret_cert'],
		                        // 必填-商户公钥证书路径
		// 即 API证书 CERTIFICATE，可在 账户中心->API安全->申请API证书 里获得
		// 文件名形如：apiclient_cert.pem
		'mch_public_cert_path' => $data['mch_public_cert_path'],
		                        // 必填-微信回调url
		// 不能有参数，如?号，空格等，否则会无法正确回调
		'notify_url' => $data['notify_url'],
		  // 选填-app 的 app_id
         'app_id' => $data['wx_appid'],
  
 
	    // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
		'mode' => Pay::MODE_NORMAL,
		                    ]
		                ],
		            ];
		Pay::config(array_merge($config, ['_force' => true]) );
 
  

       $order = [
           'out_trade_no' => $data['out_trade_no'],
           'description' => $data['description'],
           'amount' => [
              'total' =>$data['total'],
          ],
       ];
    	$result = Pay::wechat()->app($order);
 
 
		
		
		return $result;
	}
	
	
	//获取微信小程序codo
	function wx_wechat_code($data) {
		$appid = $data['wx_appid'];
		$secret = $data['wx_secret'];
		$code = $data['code'];
		$url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code=".$code;
		$data = http_curl($url);
		if (!empty($data['errcode'])) {
			return false;
		}
		return $data;
	}
	//微信小程序支付回调
	function wx_wechat_notify($data) {
	    if (isset($data['wx_appid'])) {
	        $app_id=$data['wx_appid'];
	    }  
	    if (isset($data['mini_app_id'])) {
	         $app_id=$data['mini_app_id'];
	    }  
	    
	    
	    
		$config = [
		                'wechat' => [
		                    'default' => [
		                        // 必填-商户号，服务商模式下为服务商商户号
		// 可在 https://pay.weixin.qq.com/ 账户中心->商户信息 查看
		'mch_id' => $data['mch_id'],
		                        // 必填-商户秘钥
		// 即 API v3 密钥(32字节，形如md5值)，可在 账户中心->API安全 中设置
		'mch_secret_key' => $data['mch_secret_key'],
		                        // 必填-商户私钥 字符串或路径
		// 即 API证书 PRIVATE KEY，可在 账户中心->API安全->申请API证书 里获得
		// 文件名形如：apiclient_key.pem
		'mch_secret_cert' => $data['mch_secret_cert'],
		                        // 必填-商户公钥证书路径
		// 即 API证书 CERTIFICATE，可在 账户中心->API安全->申请API证书 里获得
		// 文件名形如：apiclient_cert.pem
		'mch_public_cert_path' => $data['mch_public_cert_path'],
		
		 'app_id' => $app_id,
		                        // 必填-微信回调url
		// 不能有参数，如?号，空格等，否则会无法正确回调
		'notify_url' => '',
		                        // 选填-公众号 的 app_id
		// 可在 mp.weixin.qq.com 设置与开发->基本配置->开发者ID(AppID) 查看
		'mp_app_id' => '',
		                        // 选填-小程序 的 app_id
		'mini_app_id' => $app_id,
		                        'wechat_public_cert_path' => [
		                            '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.
		                            '/Cert/wechatPublicKey.crt',
		                        ],
		                        // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
		'mode' => Pay::MODE_NORMAL,
		                    ]
		                ],
		            ];
		Pay::config(array_merge($config, ['_force' => true]));
		$result = Pay::wechat()->callback();
		return $result;
	}
	
	
	//微信支付回调返回成功
	function wx_wechat_notify_is_ok($data) {
		$config = [
		            'wechat' => [
		                'default' => [
		                    // 必填-商户号，服务商模式下为服务商商户号
		// 可在 https://pay.weixin.qq.com/ 账户中心->商户信息 查看
		'mch_id' => $data['mch_id'],
		                    // 必填-商户秘钥
		// 即 API v3 密钥(32字节，形如md5值)，可在 账户中心->API安全 中设置
		'mch_secret_key' => $data['mch_secret_key'],
		                    // 必填-商户私钥 字符串或路径
		// 即 API证书 PRIVATE KEY，可在 账户中心->API安全->申请API证书 里获得
		// 文件名形如：apiclient_key.pem
		'mch_secret_cert' => $data['mch_secret_cert'],
		                    // 必填-商户公钥证书路径
		// 即 API证书 CERTIFICATE，可在 账户中心->API安全->申请API证书 里获得
		// 文件名形如：apiclient_cert.pem
		'mch_public_cert_path' => $data['mch_public_cert_path'],
		                    // 必填-微信回调url
		// 不能有参数，如?号，空格等，否则会无法正确回调
		'notify_url' => '',
		                    // 选填-公众号 的 app_id
		// 可在 mp.weixin.qq.com 设置与开发->基本配置->开发者ID(AppID) 查看
		'mp_app_id' => '',
		                    // 选填-小程序 的 app_id
		'mini_app_id' => $data['mini_app_id'],
		                    'wechat_public_cert_path' => [
		                        '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.
		                        '/Cert/wechatPublicKey.crt',
		                    ],
		                    // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
		'mode' => Pay::MODE_NORMAL,
		                ]
		            ],
		        ];
		Pay::config($config);
		return  $a= Pay::wechat()->success();
	}
	//微信退款功能
	function wx_wechat_refund($data) {
		$config = [
		            'wechat' => [
		                'default' => [
		                    // 必填-商户号，服务商模式下为服务商商户号
		// 可在 https://pay.weixin.qq.com/ 账户中心->商户信息 查看
		'mch_id' => $data['mch_id'],
		                    // 必填-商户秘钥
		// 即 API v3 密钥(32字节，形如md5值)，可在 账户中心->API安全 中设置
		'mch_secret_key' => $data['mch_secret_key'],
		                    // 必填-商户私钥 字符串或路径
		// 即 API证书 PRIVATE KEY，可在 账户中心->API安全->申请API证书 里获得
		// 文件名形如：apiclient_key.pem
		'mch_secret_cert' => $data['mch_secret_cert'],
		                    // 必填-商户公钥证书路径
		// 即 API证书 CERTIFICATE，可在 账户中心->API安全->申请API证书 里获得
		// 文件名形如：apiclient_cert.pem
		'mch_public_cert_path' => $data['mch_public_cert_path'],
		                    // 必填-微信回调url
		// 不能有参数，如?号，空格等，否则会无法正确回调
		'notify_url' => '',
		                    // 选填-公众号 的 app_id
		// 可在 mp.weixin.qq.com 设置与开发->基本配置->开发者ID(AppID) 查看
		'mp_app_id' => '',
		                    // 选填-小程序 的 app_id
		'mini_app_id' => $data['mini_app_id'],
		                    'wechat_public_cert_path' => [
		                        '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.
		                        '/Cert/wechatPublicKey.crt',
		                    ],
		                    // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
		'mode' => Pay::MODE_NORMAL,
		                ]
		            ],
		        ];
		Pay::config(array_merge($config, ['_force' => true]));
		if (empty($data['reason'])) {
			$data['reason']='商品';
		}
		$order = [
		            'out_trade_no' => $data['out_trade_no'],
		            'out_refund_no' =>$data['out_refund_no'],
		            'notify_url' =>$data['notify_url'],
		            'amount' => [
		                'refund' =>$data['refund'],
		                'total' => $data['total'],
		                'currency' => 'CNY',
		            ],
		            'reason' =>$data['reason'], 
		        ];
		$result = Pay::wechat()->refund($order);
		return $result;
	}
	//微信公众号支付
	function wx_wechat_mp_pay($data) {
		$config = [
		    'wechat' => [
		        'default' => [
		             'mch_id' => $data['mch_id'],
		              'mp_app_id' => $data['mp_app_id'],
		            // 选填-服务商模式下，子公众号 的 app_id
		'sub_mp_app_id' =>  $data['mp_app_id'],
		             'mch_secret_key' => $data['mch_secret_key'],//v3 key
		'mch_secret_cert' => $data['mch_secret_cert'],
		             'mch_public_cert_path' => $data['mch_public_cert_path'],
		             'notify_url' => $data['notify_url'],
		            // 选填-微信平台公钥证书路径, optional，强烈建议 php-fpm 模式下配置此参数
		'wechat_public_cert_path' => [
		                '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.'/Cert/wechatPublicKey.crt',
		             ],
		            // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
		'mode' => Pay::MODE_NORMAL,
		        ]
		       ],
		      ];
		Pay::config(array_merge($config, ['_force' => true]));
		$order = [
		              'out_trade_no' => $data['out_trade_no'],
		               'description' => $data['description'],
		               'amount' => [
		                  'total' => $data['total'],
		              ],
		              'payer' => [
		              'openid' => $data['openid'],
		              ],
		          ];
		$result = Pay::wechat()->mp($order);
		return $result;
	}
}