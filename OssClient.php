<?php
/**
 * Class OssClient
 * 参考文档 https://open.fuiou.com/authapply/getFileList.do?module=2
 */
error_reporting(E_ALL ^ E_NOTICE);
include('OssBaseClient.php');
include('OssLog.php');
class OssClient extends OssBaseClient
{
    private $timeOutSeconds = 30;

    protected $accessAppKey = null; //接口名称 h5Pay bindMsg unbind bindCommit
    protected $accessSecret = null;//商户秘钥
    protected $params = [];
    protected $postData = [];
    protected $postErrorMsg = '';
    protected $postUrl = '';
    protected $debug = true;

    /**
     * @param $accessAppKey string 应用类型字符串
     * @param $accessSecret string 商户秘钥
     * @param $params array 支付参数
    */
    public function __construct($accessAppKey,$accessSecret,$params)
    {
        parent::__construct($accessAppKey,$accessSecret,$params);
    }

    private function beforeRun(){
        if(!in_array($this->accessAppKey,array_keys($this->checkFieldList))){
            return ['code'=>204,'msg'=>'请求接口不存在'];
        }

        $this->checkParams($this->params);
        if($this->postErrorMsg){
            return ['code'=>201,'msg'=>$this->postErrorMsg];
        }
        if(!$this->postData){
            return ['code'=>202,'msg'=>'参数不能为空'];
        }

        $this->postUrl = $this->getPostUrl($this->accessAppKey);
        if(!$this->postUrl){
            return ['code'=>203,'msg'=>'请求地址不能为空'];
        }
        return ['code'=>200,'msg'=>''];
    }

    //H5支付
    public function pay(){
        $checkResult = $this->beforeRun();
        if($checkResult['code'] != 200){
            return $checkResult;
        }
        $secret = $this->getSign($this->postData);

        $data['VERSION'] = '2.0';
        $data['ENCTP'] = 1;
        $data['LOGOTP'] = $this->postData['LOGOTP'];
        $data['MCHNTCD'] = $this->postData['MCHNTCD'];
        $data['FM'] = str_replace(' ', '', $secret);

        if($this->debug){
            $logName = 'pay-'.date('Ymd').'.txt';
            file_put_contents($logName,print_r($data,true));
        }

        $html = '<html><head></head><body><h2>支付接口_验证实例</h2>
                    <form action="'. $this->postUrl .'" method="post" id="pay">
                    加密标志：<input type="text" name="ENCTP" value="'. $data['ENCTP'] .'" style="width:300px;"><br>
                    版本号：<input type="text" name="VERSION" value="'. $data['VERSION'] .'" style="width:300px;"><br>
                    商户代码：<input type="text" name="MCHNTCD" value="'. $data['MCHNTCD'] .'" style="width:300px;"><br>
                    logo标志：<input type="text" name="LOGOTP" value="'. $data['LOGOTP'] .'" style="width:300px;"><br>
                    订单信息：<input type="text" name="FM" value="'. $data['FM'] .'" style="width:300px;"><br>
                    <input type="submit" name="submit" value="提交"><br>
                    </form>
                    </body>
                    </html>
                    ';
        echo $html;
        /*$content = http_build_query($data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $content,
                'timeout' => $this->timeOutSeconds // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($this->payUrl, false, $context);

        return $result;*/
    }

    //绑定协议卡短信验证
    public function bindMsg(){
        $checkResult = $this->beforeRun();
        if($checkResult['code'] != 200){
            return $checkResult;
        }
        $secret = $this->getSign($this->postData);
        $data['MCHNTCD'] = $this->postData['MCHNTCD'];
        $data['APIFMS'] = str_replace(' ', '', $secret);

        if($this->debug){
            $logName = 'bindMsg-'.date('Ymd').'.txt';
            file_put_contents($logName,print_r($data,true));
        }

        $content = http_build_query($data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $content,
                'timeout' => $this->timeOutSeconds // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($this->postUrl, false, $context);
        $r = $this->resolveResponseData($result,['MCHNTSSN']);
        if($r['code'] !== 200){
            $fileName = "bindMsg.txt";
            $ossLog = new OssLog($fileName);
            $message = sprintf('请求地址：%s  请求参数：%s 接口返回：%s',$this->postUrl,print_r($this->postData,true),print_r($r,true));
            $ossLog->write($message);
        }
        return $r;
        /*$html = '<h2>协议卡绑定短信接口_验证实例</h2>
                    <form action="'. $this->bindMsgUrl .'" method="post"><br>
                    商户代码：<input type="text" name="MCHNTCD" value="'. $data['MCHNTCD'] .'" style="width:300px;"><br>
                    订单信息：<input type="text" name="APIFMS" value="'. $data['APIFMS'] .'" style="width:300px;"><br>
                    <input type="submit" name="submit" value="提交"><br>
                    </form>';
        echo $html;*/
    }

    //协议卡绑定
    public function bindCommit(){
        $checkResult = $this->beforeRun();
        if($checkResult['code'] != 200){
            return $checkResult;
        }
        $secret = $this->getSign($this->postData);
        $data['MCHNTCD'] = $this->postData['MCHNTCD'];
        $data['APIFMS'] = str_replace(' ', '', $secret);
        if($this->debug){
            $logName = 'bindCommit-'.date('Ymd').'.txt';
            file_put_contents($logName,print_r($data,true));
        }
        $content = http_build_query($data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $content,
                'timeout' => $this->timeOutSeconds // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($this->postUrl, false, $context);
        $r = $this->resolveResponseData($result,['PROTOCOLNO']);
        if($r['code'] !== 200){
            $fileName = "bindCommit.txt";
            $ossLog = new OssLog($fileName);
            $message = sprintf('请求地址：%s  请求参数：%s 接口返回：%s',$this->postUrl,print_r($this->postData,true),print_r($r,true));
            $ossLog->write($message);
        }
        return $r;

        /*$html = '<h2>协议卡绑定接口_验证实例</h2>
                    <form action="'. $this->bindCommitUrl .'" method="post"><br>
                    商户代码：<input type="text" name="MCHNTCD" value="'. $data['MCHNTCD'] .'" style="width:300px;"><br>
                    订单信息：<input type="text" name="APIFMS" value="'. $data['APIFMS'] .'" style="width:300px;"><br>
                    <input type="submit" name="submit" value="提交"><br>
                    </form>';
        echo $html;*/
    }

    //协议卡解绑
    public function unbind(){
        $checkResult = $this->beforeRun();
        if($checkResult['code'] != 200){
            return $checkResult;
        }
        $secret = $this->getSign($this->postData);
        $data['MCHNTCD'] = $this->postData['MCHNTCD'];
        $data['APIFMS'] = str_replace(' ', '', $secret);
        if($this->debug){
            $logName = 'unbind-'.date('Ymd').'.txt';
            file_put_contents($logName,print_r($data,true));
        }
        $content = http_build_query($data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $content,
                'timeout' => $this->timeOutSeconds // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($this->postUrl, false, $context);
        $r = $this->resolveResponseData($result,['PROTOCOLNO','USERID','MCHNTCD']);
        if($r['code'] !== 200){
            $fileName = "unbind.txt";
            $ossLog = new OssLog($fileName);
            $message = sprintf('请求地址：%s  请求参数：%s 接口返回：%s',$this->postUrl,print_r($this->postData,true),print_r($r,true));
            $ossLog->write($message);
        }
        return $r;
    }

    //协议支付
    public function orderPay(){
        $checkResult = $this->beforeRun();
        if($checkResult['code'] != 200){
            return $checkResult;
        }
        $secret = $this->getSign($this->postData);
        $data['MCHNTCD'] = $this->postData['MCHNTCD'];
        $data['APIFMS'] = str_replace(' ', '', $secret);
        if($this->debug){
            $logName = 'orderPay-'.date('Ymd').'.txt';
            file_put_contents($logName,print_r($data,true));
        }
        $content = http_build_query($data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $content,
                'timeout' => $this->timeOutSeconds // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($this->postUrl, false, $context);
        $r = $this->resolveResponseData($result,['MCHNTCD','USERID','MCHNTORDERID','ORDERID','BANKCARD','AMT','PROTOCOLNO']);
        if($r['code'] !== 200){
            $fileName = "orderPay.txt";
            $ossLog = new OssLog($fileName);
            $message = sprintf('请求地址：%s  请求参数：%s  接口返回：%s',$this->postUrl,print_r($this->postData,true),print_r($r,true));
            $ossLog->write($message);
        }
        return $r;
    }

    //处理协议支付异步回调
    public function notify(){
        if($this->postErrorMsg){
            return ['code'=>201,'errorMsg'=>$this->postErrorMsg];
        }
        if(!$this->postData){
            return ['code'=>202,'errorMsg'=>'参数不能为空'];
        }
        $r = ['code'=>300,'msg'=>'签名验证失败','data'=>[]];
        $secret = $this->getSign($this->postData);
        if($secret === $this->postData['SIGN']){
            $r['code'] = 200;
            $r['msg'] = '签名验证成功';
            $r['data'] = $this->postData;
        }
        return $r;
    }

    //PC认证支付
    public function dirPayGate(){
        $checkResult = $this->beforeRun();
        if($checkResult['code'] != 200){
            return $checkResult;
        }
        $secret = $this->getSign($this->postData);
        $data['mchnt_cd'] = $this->postData['MCHNTCD'];
        $data['order_id'] = $this->postData['MCHNTORDERID'];
        $data['order_amt'] = $this->postData['AMT'];
        $data['user_type'] = $this->postData['TYPE'];
        $data['page_notify_url'] = $this->postData['HOMEURL'];
        $data['back_notify_url'] = $this->postData['BACKURL'];
        $data['card_no'] = $this->postData['CARDNO'];
        $data['cert_type'] = $this->postData['IDTYPE'];
        $data['cert_no'] = $this->postData['IDCARD'];
        $data['cardholder_name'] = $this->postData['USERNAME'];
        $data['user_id'] = $this->postData['USERID'];
        $data['RSA'] = $secret;
        if($this->debug){
            $logName = 'dirPayGate-'.date('Ymd').'.txt';
            file_put_contents($logName,print_r($data,true));
        }
        $html = '<html>
                    <head></head>
                    <body>
                        <form action="'. $this->postUrl .'"  method="post">
                            <input name="mchnt_cd" type="text" value="'. $this->postData['MCHNTCD'] .'" /><br>
                            <input name="order_id" type="text" value="'. $this->postData['MCHNTORDERID'] .'"/><br>
                            <input name="order_amt" type="text" value="'. $this->postData['AMT']  .'"/><br>
                            <input name="user_type" type="text" value="'. $this->postData['TYPE'] .'"/><br>
                            <input name="user_type" type="text" value="'. $this->postData['HOMEURL'] .'"/><br>
                            <input name="user_type" type="text" value="'. $this->postData['BACKURL'] .'"/><br>
                            <input name="user_type" type="text" value="'. $this->postData['CARDNO'] .'"/><br>
                            <input name="user_type" type="text" value="'. $this->postData['IDTYPE'] .'"/><br>
                            <input name="user_type" type="text" value="'. $this->postData['IDCARD'] .'"/><br>
                            <input name="user_type" type="text" value="'. $this->postData['USERNAME'] .'"/><br>
                            <input name="user_type" type="text" value="'. $this->postData['USERID'] .'"/><br>
                            <input type="submit" name="submit" value="提交"><br>
                        </form>
                    </body>
                </html>';
        echo $html;
        /*$content = http_build_query($data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $content,
                'timeout' => $this->timeOutSeconds // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($this->postUrl, false, $context);
        $r = $this->resolveResponseData($result,['MCHNTCD','USERID','MCHNTORDERID','ORDERID','BANKCARD','AMT','PROTOCOLNO']);
        if($r['code'] !== 200){
            $fileName = "dirPayGate.txt";
            $ossLog = new OssLog($fileName);
            $message = sprintf('请求地址：%s  请求参数：%s  接口返回：%s',$this->postUrl,print_r($this->postData,true),print_r($r,true));
            $ossLog->write($message);
        }
        return $r;*/
    }
}