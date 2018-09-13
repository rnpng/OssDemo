<?php
error_reporting(E_ALL ^ E_NOTICE);
include('OssUtil.php');
class OssBaseClient
{
    /*private $payUrl = 'http://www-1.fuiou.com:18670/mobile_pay/h5pay/payAction.pay'; //H5支付
    private $bindMsgUrl = 'http://www-1.fuiou.com:18670/mobile_pay/newpropay/bindMsg.pay';//绑定协议短信验证
    private $bindCommitUrl = 'http://www-1.fuiou.com:18670/mobile_pay/newpropay/bindCommit.pay';//绑定协议
    private $unbindUrl = 'http://www-1.fuiou.com:18670/mobile_pay/newpropay/unbind.pay';//解绑协议
    private $orderPayUrl = 'http://www-1.fuiou.com:18670/mobile_pay/newpropay/order.pay';//协议支付
    */
    protected $debug = false;

    protected $checkFieldList = [
        'h5Pay'=> [
            'MCHNTCD'=>['required'=>1,'tip'=>'商户代码必需'],
            'TYPE'=>['required'=>1,'tips'=>'交易类型必需'],
            'MCHNTORDERID'=>['required'=>1,'tips'=>'商户订单号必需'],
            'USERID'=>['required'=>1,'tips'=>'用户编号必需'],
            'AMT'=>['required'=>1,'tips'=>'交易金额必需'],
            'BANKCARD'=>['required'=>1,'tips'=>'银行卡号必需'],
            'BACKURL'=>['required'=>1,'tips'=>'后台通知URL必需'],
            'REURL'=>['required'=>1,'tips'=>'支付失败URL必需'],
            'HOMEURL'=>['required'=>1,'tips'=>'页面通知URL必需'],
            'NAME'=>['required'=>1,'tips'=>'用户姓名必需'],
            'VERSION'=>['required'=>0,'tips'=>''],
            'ENCTP'=>['required'=>0,'tips'=>''],
            'LOGOTP'=>['required'=>0,'tips'=>''],
            'IDNO'=>['required'=>0,'tips'=>''],
            'IDTYPE'=>['required'=>0,'tips'=>''],
            'SIGNTP'=>['required'=>1,'tips'=>'加密方式'],
        ],
        'bindMsg'=>[
            'VERSION'=>['required'=>1,'tips'=>'版本号必需'],
            'MCHNTCD'=>['required'=>1,'tips'=>'商户代码必需'],
            'USERID'=>['required'=>1,'tips'=>'用户编号必需'],
            'TRADEDATE'=>['required'=>1,'tips'=>'交易请求日期必需'],
            'MCHNTSSN'=>['required'=>1,'tips'=>'商户流水号必需'],
            'ACCOUNT'=>['required'=>1,'tips'=>'银行卡账户名称必需'],
            'CARDNO'=>['required'=>1,'tips'=>'银行卡号必需'],
            'IDTYPE'=>['required'=>1,'tips'=>'证件类型必需'],
            'IDCARD'=>['required'=>1,'tips'=>'证件号码必需'],
            'MOBILENO'=>['required'=>1,'tips'=>'银行卡预留手机号码必需'],
        ],
        'unbind'=>[
            'VERSION'=>['required'=>1,'tips'=>'版本号必需'],
            'MCHNTCD'=>['required'=>1,'tips'=>'商户代码必需'],
            'USERID'=>['required'=>1,'tips'=>'用户编号必需'],
            'PROTOCOLNO'=>['required'=>1,'tips'=>'协议号必需']
        ],
        'bindCommit'=>[
            'VERSION'=>['required'=>1,'tips'=>'版本号必需'],
            'MCHNTCD'=>['required'=>1,'tips'=>'商户代码必需'],
            'USERID'=>['required'=>1,'tips'=>'用户编号必需'],
            'TRADEDATE'=>['required'=>1,'tips'=>'交易请求日期必需'],
            'MCHNTSSN'=>['required'=>1,'tips'=>'商户流水号必需'],
            'ACCOUNT'=>['required'=>1,'tips'=>'银行卡账户名称必需'],
            'CARDNO'=>['required'=>1,'tips'=>'银行卡号必需'],
            'IDTYPE'=>['required'=>1,'tips'=>'证件类型必需'],
            'IDCARD'=>['required'=>1,'tips'=>'证件号码必需'],
            'MOBILENO'=>['required'=>1,'tips'=>'银行卡预留手机号码必需'],
            'MSGCODE'=>['required'=>1,'tips'=>'短信验证码必需']
        ],
        'orderPay'=>[
            'VERSION'=>['required'=>1,'tips'=>'版本号必需'],
            'USERIP'=>['required'=>1,'tips'=>'客服IP必需'],
            'MCHNTCD'=>['required'=>1,'tips'=>'商户代码必需'],
            'TYPE'=>['required'=>1,'tips'=>'交易类型必需'],
            'MCHNTORDERID'=>['required'=>1,'tips'=>'商户订单号必需'],
            'USERID'=>['required'=>1,'tips'=>'用户编号必需'],
            'AMT'=>['required'=>1,'tips'=>'交易金额必需'],
            'PROTOCOLNO'=>['required'=>1,'tips'=>'协议号必需'],
            'NEEDSENDMSG'=>['required'=>1,'tips'=>'是否需要发送短信必需'],
            'BACKURL'=>['required'=>1,'tips'=>'后台通知URL必需'],
            'REM1'=>['required'=>0,'tips'=>''],
            'REM2'=>['required'=>0,'tips'=>''],
            'REM3'=>['required'=>0,'tips'=>''],
            'SIGNTP'=>['required'=>1,'tips'=>'加密方式'],
        ],
        'notify'=>[
            'VERSION'=>['required'=>1,'tips'=>'版本号必须'],
            'TYPE'=>['required'=>1,'tips'=>'交易类型必须'],
            'RESPONSECODE'=>['required'=>1,'tips'=>'响应代码必须'],
            'RESPONSEMSG'=>['required'=>1,'tips'=>'响应中文描述必须'],
            'MCHNTCD'=>['required'=>1,'tips'=>'商户代码必须'],
            'USERID'=>['required'=>1,'tips'=>'用户编号必须'],
            'MCHNTORDERID'=>['required'=>1,'tips'=>'商户订单号必需'],
            'ORDERID'=>['required'=>1,'tips'=>'富友订单号'],
            'PROTOCOLNO'=>['required'=>1,'tips'=>'协议号必需'],
            'BANKCARD'=>['required'=>1,'tips'=>'银行卡号必须'],
            'AMT'=>['required'=>1,'tips'=>'交易金额必需'],
            'REM1'=>['required'=>0,'tips'=>''],
            'REM2'=>['required'=>0,'tips'=>''],
            'REM3'=>['required'=>0,'tips'=>''],
            'SIGNTP'=>['required'=>1,'tips'=>'加密方式必须'],
            'SIGN'=>['required'=>1,'tips'=>'签名必须']
        ]
    ];

    private $postUrlList = [
        'h5Pay'=>[
            0=>'http://www-1.fuiou.com:18670/mobile_pay/h5pay/payAction.pay',
            1=>'https://mpay.fuiou.com:16128/h5pay/payAction.pay'
        ],
        'bindMsg'=>[
            0=>'http://www-1.fuiou.com:18670/mobile_pay/newpropay/bindMsg.pay',
            1=>'https://mpay.fuiou.com/newpropay/bindMsg.pay'
        ],
        'bindCommit'=>[
            0=>'http://www-1.fuiou.com:18670/mobile_pay/newpropay/bindCommit.pay',
            1=>'https://mpay.fuiou.com/newpropay/bindCommit.pay'
        ],
        'unbind'=>[
            0=>'http://www-1.fuiou.com:18670/mobile_pay/newpropay/unbind.pay',
            1=>'https://mpay.fuiou.com/newpropay/unbind.pay'
        ],
        'orderPay'=>[
            0=>'http://www-1.fuiou.com:18670/mobile_pay/newpropay/order.pay',
            1=>'https://mpay.fuiou.com/newpropay/order.pay'
        ]
    ];
    protected $postUrl = '';
    protected $accessAppKey = null; //接口名称 h5Pay bindMsg unbind bindCommit orderPay
    protected $accessSecret = null;//商户秘钥
    protected $params = [];
    protected $postData = [];
    protected $postErrorMsg = '';

    /**
     * @param $accessAppKey string 应用类型字符串
     * @param $accessSecret string 商户秘钥
     * @param $params array 支付参数
    */
    public function __construct($accessAppKey,$accessSecret,$params)
    {
        $this->accessAppKey = $accessAppKey;
        $this->accessSecret = $accessSecret;
        $this->params = $params;
    }

    protected function checkParams($params){
        if($params){
            $postData = [];
            $fieldList = array_keys($this->checkFieldList[$this->accessAppKey]);
            foreach($fieldList as $field){
                if($this->checkFieldList[$this->accessAppKey][$field]['required'] && !isset($params[$field])){
                    $this->postErrorMsg = $this->checkFieldList[$this->accessAppKey][$field]['tips'];
                    return false;
                }
                if(in_array($field,$fieldList)){
                    $postData[$field] = $params[$field];
                }
            }
            $this->postData = $postData;
        }
    }

    protected function getPostUrl($accessAppKey){
        $index = $this->debug ? 0 : 1;
        return $this->postUrlList[$accessAppKey][$index] ? $this->postUrlList[$accessAppKey][$index] : '';
    }


    //解析接口返回的结果
    public function resolveResponseData($result,$fields){
        $r = ['code'=>300,'msg'=>'接口无返回'];
        if(!$result){
            return $r;
        }

        $xmlString = \OSS\OssUtil::decryptForDES($result,$this->accessSecret);
        if(!$xmlString){
            $r['code']=301;
            $r['msg']='解析异常';
            return $r;
        }

        $xml =simplexml_load_string($xmlString);
        if($xml){
            $xmlJson= json_encode($xml);
            $xmlArr=json_decode($xmlJson,true);
            if($xmlArr && $xmlArr['RESPONSECODE'] == '0000'){
                $r['code'] = 200;
                $r['msg'] = $xmlArr['RESPONSEMSG'];
                foreach($fields as $field){
                    $temp[$field] = $xmlArr[$field];
                }
                $r['data'] = $temp;
            }else{
                $r['code'] = $xmlArr['RESPONSECODE'] ? $xmlArr['RESPONSECODE'] : 301;
                $r['msg'] = $xmlArr['RESPONSEMSG'] ? $xmlArr['RESPONSEMSG'] : '接口返回结果异常';
            }
        }
        return $r;
    }


    //获取签名
    protected function getSign($data){
        if($data){
            foreach($data as $key=>$val){
                $val = trim($val);
                $val = stripslashes($val);
                $data[$key] = htmlspecialchars($val);
            }
        }
        if($this->accessAppKey == 'h5Pay'){
            $sign = $data['TYPE']."|".$data['VERSION']."|".$data['MCHNTCD']."|".$data['MCHNTORDERID'] ."|".$data['USERID']."|".$data['AMT']."|".$data['BANKCARD']."|".$data['BACKURL']."|".
                $data['NAME']."|".$data['IDNO']."|".$data['IDTYPE']."|".$data['LOGOTP']."|".$data['HOMEURL']."|".$data['REURL']."|".$this->accessSecret;
            $sign = str_replace(' ', '', $sign);
            $fm = "<ORDER>"
                ."<VERSION>".$data['VERSION']."</VERSION>"
                ."<LOGOTP>".$data['LOGOTP']."</LOGOTP>"
                ."<MCHNTCD>".$data['MCHNTCD']."</MCHNTCD> "
                ."<TYPE>".$data['TYPE']."</TYPE>"
                ."<MCHNTORDERID>".$data['MCHNTORDERID']."</MCHNTORDERID>"
                ."<USERID>".$data['USERID']."</USERID>"
                ."<AMT>".$data['AMT']."</AMT>"
                ."<BANKCARD>".$data['BANKCARD']."</BANKCARD>"
                ."<NAME>".$data['NAME']."</NAME>"
                ."<IDTYPE>".$data['IDTYPE']."</IDTYPE>"
                ."<IDNO>".$data['IDNO']."</IDNO>"
                ."<BACKURL>".$data['BACKURL']."</BACKURL>"
                ."<HOMEURL>".$data['HOMEURL']."</HOMEURL>"
                ."<REURL>".$data['REURL']."</REURL>"
                ."<REM1>".$data['REM1']."</REM1>"
                ."<REM2>".$data['REM2']."</REM2>"
                ."<REM3>".$data['REM3']."</REM3>"
                ."<SIGNTP>".$data['SIGNTP']."</SIGNTP>"
                ."<SIGN>".md5($sign)."</SIGN>"
                ."</ORDER>";
            $secret = \OSS\OssUtil::encryptForDES($fm,$this->accessSecret);
            return $secret;
        }else if($this->accessAppKey == 'bindMsg'){
            //协议卡绑定短信验证
            $sign = $data['VERSION']."|".$data['MCHNTSSN']."|".$data['MCHNTCD']."|".$data['USERID'] ."|".$data['ACCOUNT']."|".$data['CARDNO']."|".$data['IDTYPE']."|".$data['IDCARD']."|".
                $data['MOBILENO']."|".$this->accessSecret;
            $sign = str_replace(' ', '', $sign);
            $fm = '<REQUEST>
                        <VERSION>'. $data['VERSION'] .'</VERSION>
                        <MCHNTCD>'. $data['MCHNTCD'] .'</MCHNTCD>
                        <USERID>'. $data['USERID'] .'</USERID>
                        <TRADEDATE>'. $data['TRADEDATE'] .'</TRADEDATE>
                        <MCHNTSSN>'. $data['MCHNTSSN'] .'</MCHNTSSN>
                        <ACCOUNT>'. $data['ACCOUNT'] .'</ACCOUNT>
                        <CARDNO>'. $data['CARDNO'] .'</CARDNO>
                        <IDTYPE>'. $data['IDTYPE'] .'</IDTYPE>
                        <IDCARD>'. $data['IDCARD'] .'</IDCARD>
                        <MOBILENO>'. $data['MOBILENO'] .'</MOBILENO>
                        <CVN></CVN>
                        <SIGN>'. md5($sign) .'</SIGN>
                    </REQUEST>
            ';
            $secret = \OSS\OssUtil::encryptForDES($fm,$this->accessSecret);
            return $secret;
        }else if($this->accessAppKey == 'bindCommit'){
            //协议卡绑定
            $sign = $data['VERSION']."|".$data['MCHNTSSN']."|".$data['MCHNTCD']."|".$data['USERID'] ."|".$data['ACCOUNT']."|".$data['CARDNO']."|".$data['IDTYPE']."|".$data['IDCARD']."|".
                $data['MOBILENO']."|". $data['MSGCODE'] . "|" .$this->accessSecret;
            $sign = str_replace(' ', '', $sign);
            $fm = '<REQUEST>
                        <VERSION>'. $data['VERSION'] .'</VERSION>
                        <MCHNTCD>'. $data['MCHNTCD'] .'</MCHNTCD>
                        <USERID>'. $data['USERID'] .'</USERID>
                        <TRADEDATE>'. $data['TRADEDATE'] .'</TRADEDATE>
                        <MCHNTSSN>'. $data['MCHNTSSN'] .'</MCHNTSSN>
                        <ACCOUNT>'. $data['ACCOUNT'] .'</ACCOUNT>
                        <CARDNO>'. $data['CARDNO'] .'</CARDNO>
                        <IDTYPE>'. $data['IDTYPE'] .'</IDTYPE>
                        <IDCARD>'. $data['IDCARD'] .'</IDCARD>
                        <MOBILENO>'. $data['MOBILENO'] .'</MOBILENO>
                        <MSGCODE>'. $data['MSGCODE'] .'</MSGCODE>
                        <SIGN>'. md5($sign) .'</SIGN>
                    </REQUEST>
            ';
            $secret = \OSS\OssUtil::encryptForDES($fm,$this->accessSecret);
            return $secret;
        }else if($this->accessAppKey == 'unbind'){
            //协议解绑
            $sign = $data['VERSION']."|".$data['MCHNTCD']."|".$data['USERID']."|".$data['PROTOCOLNO'] . "|" .$this->accessSecret;
            $sign = str_replace(' ', '', $sign);
            $fm = '<REQUEST>
                        <VERSION>'. $data['VERSION'] .'</VERSION>
                        <MCHNTCD>'. $data['MCHNTCD'] .'</MCHNTCD>
                        <USERID>'. $data['USERID'] .'</USERID>
                        <PROTOCOLNO>'. $data['PROTOCOLNO'] .'</PROTOCOLNO>
                        <SIGN>'. md5($sign) .'</SIGN>
                    </REQUEST>
            ';
            $secret = \OSS\OssUtil::encryptForDES($fm,$this->accessSecret);
            return $secret;
        }else if($this->accessAppKey == 'orderPay'){
            //协议支付
            $sign = $data['TYPE']."|".$data['VERSION']."|".$data['MCHNTCD']."|".$data['MCHNTORDERID'] ."|".$data['USERID']."|".$data['PROTOCOLNO']."|".$data['AMT']."|".$data['BACKURL']."|".
                $data['USERIP']."|".$this->accessSecret;
            $sign = str_replace(' ', '', $sign);
            $fm = '<REQUEST>
                        <VERSION>'. $data['VERSION'] .'</VERSION>
                        <USERIP>'. $data['USERIP'] .'</USERIP>
                        <MCHNTCD>'. $data['MCHNTCD'] .'</MCHNTCD>
                        <TYPE>'. $data['TYPE'] .'</TYPE>
                        <MCHNTORDERID>'. $data['MCHNTORDERID'] .'</MCHNTORDERID>
                        <USERID>'. $data['USERID'] .'</USERID>
                        <AMT>'. $data['AMT'] .'</AMT>
                        <PROTOCOLNO>'. $data['PROTOCOLNO'] .'</PROTOCOLNO>
                        <NEEDSENDMSG>'. $data['NEEDSENDMSG'] .'</NEEDSENDMSG>
                        <BACKURL>'. $data['BACKURL'] .'</BACKURL>
                        <REM1>'. $data['REM1'] .'</REM1>
                        <REM2>'. $data['REM2'] .'</REM2>
                        <REM3>'. $data['REM3'] .'</REM3>
                        <SIGNTP>'. $data['SIGNTP'] .'</SIGNTP>
                        <SIGN>'. md5($sign) .'</SIGN>
                    </REQUEST>
            ';
            $secret = \OSS\OssUtil::encryptForDES($fm,$this->accessSecret);
            return $secret;
        }else if($this->accessAppKey == 'notify'){
            $sign = $data['TYPE']."|".$data['VERSION']."|".$data['RESPONSECODE']."|".$data['MCHNTCD'] ."|".$data['MCHNTORDERID']."|".$data['ORDERID']."|".$data['AMT']."|".$data['BANKCARD']."|".$this->accessSecret;
            $sign = str_replace(' ', '', $sign);
            $sign = md5($sign);
            return $sign;
        }
    }
}