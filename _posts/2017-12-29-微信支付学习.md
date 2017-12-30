## 微信支付

### 支付模式

1. 公众号支付

    公众号支付是用户在微信中打开商户的H5页面，商户在H5页面通过调用微信支付提供的JSAPI接口调起微信支付模块完成支付。应用场景有：
    
  - 用户在微信公众账号内进入商家公众号，打开某个主页面，完成支付
  - 用户的好友在朋友圈、聊天窗口等分享商家页面连接，用户点击链接打开商家页面，完成支付
  - 将商户页面转换成二维码，用户扫描二维码后在微信浏览器中打开页面后完成支付
  
2. 支付账户

  - 商户在微信公众平台(申请扫码支付、公众号支付)或开放平台(申请APP支付)按照相应提示，申请相应微信支付模式。
  - 微信支付工作人员审核资料无误后开通相应的微信支付权限。
  - 微信支付申请审核通过后，商户在申请资料填写的邮箱中收取到由微信支付小助手发送的邮件，此邮件包含开发时需要使用的支付账户信息，见图3.1所示。
    
3. 开发步骤

一、设置支付目录
请确保实际支付时的请求目录与后台配置的目录一致，否则将无法成功唤起微信支付。
在微信商户平台（pay.weixin.qq.com）设置您的公众号支付支付目录，设置路径：商户平台-->产品中心-->开发配置，如图7.7所示。公众号支付在请求支付的时候会校验请求来源是否有在商户平台做了配置，所以必须确保支付目录已经正确的被配置，否则将验证失败，请求支付不成功。
二、设置授权域名
开发公众号支付时，在统一下单接口中要求必传用户openid，而获取openid则需要您在公众平台设置获取openid的域名，只有被设置过的域名才是一个有效的获取openid的域名，否则将获取失败。具体界面如图7.8所示：


4. 支付流程

  - 必要配置
  ```
 use EasyWeChat\Factory;
 
 $options = [
     // 必要配置
     'app_id'             => 'xxxx',
     'mch_id'             => 'your-mch-id',
     'key'                => 'key-for-signature',   // API 密钥
 
     // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
     'cert_path'          => 'path/to/your/cert.pem', // XXX: 绝对路径！！！！
     'key_path'           => 'path/to/your/key',      // XXX: 绝对路径！！！！
 
     'notify_url'         => '默认的订单回调地址',     // 你也可以在下单时单独设置来想覆盖它
 ];
 
 $app = Factory::payment($options);
```
  - 订单
    - 统一下单
    
         注： 参数 appid, mch_id, nonce_str, sign, sign_type 可不用传入
```
      $result = $app->order->unify([
          'body' => '腾讯充值中心-QQ会员充值',
          'out_trade_no' => '20150806125346',
          'total_fee' => 88,
          'spbill_create_ip' => '123.12.12.123', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
          'notify_url' => 'https://pay.weixin.qq.com/wxpay/pay.action', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
          'trade_type' => 'JSAPI',
          'openid' => 'oUpF8uMuAJO_M2pxb1Q9zNjWeS6o',
      ]);
      
      // $result:
      //{
      //  "xml": {
      //    "return_code": "SUCCESS",
      //    "return_msg": "OK",
      //    "appid": "wx2421b1c4390ec4sb",
      //    "mch_id": "10000100",
      //    "nonce_str": "IITRi8Iabbblz1J",
      //    "openid": "oUpF8uMuAJO_M2pxb1Q9zNjWeSs6o",
      //    "sign": "7921E432F65EB8ED0CE9755F0E86D72F2",
      //    "result_code": "SUCCESS",
      //    "prepay_id": "wx201411102639507cbf6ffd8b0779950874",
      //    "trade_type": "JSAPI"
      //  }
      //}
```

   - 查询订单
   
       该接口提供所有微信支付订单的查询，商户可以通过该接口主动查询订单状态，完成下一步的业务逻辑。
   
       需要调用查询接口的情况：
       
       当商户后台、网络、服务器等出现异常，商户系统最终未接收到支付通知；
       调用支付接口后，返回系统错误或未知交易状态情况；
       调用被扫支付API，返回USERPAYING的状态；
       调用关单或撤销接口API之前，需确认支付状态；
       根据商户订单号查询
       
       $app->order->queryByOutTradeNumber("商户系统内部的订单号（out_trade_no）");
       根据微信订单号查询
       
       $app->order->queryByTransactionId("微信订单号（transaction_id）");
       关闭订单
       
       注意：订单生成后不能马上调用关单接口，最短调用时间间隔为5分钟。
       $app->order->close(商户系统内部的订单号（out_trade_no）);
















