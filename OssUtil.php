<?php
namespace OSS;
/**
 * Class OssUtil
 *
 * Oss工具类 主要封装支付的加密算法
 *
 * @package OSS
 */
class OssUtil
{
    //加密算法
    public static  function encryptForDES($input,$key)
    {
        $size = mcrypt_get_block_size('des','ecb');
        $input = self::pkcs5_pad($input, $size);
        $td = mcrypt_module_open('des', '', 'ecb', '');
        $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        @mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        return $data;
    }

    //解密函数
    public static function decryptForDES($input, $key)
    {
        $input = base64_decode($input);
        $size = mcrypt_get_block_size('des', 'ecb');
        $td = mcrypt_module_open('des', '', 'ecb', '');
        $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        @mcrypt_generic_init($td, $key, $iv);
        $data = mdecrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = self::pkcs5_unpad($data, $size);
        return $data;
    }


    public static  function pkcs5_pad ($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public static  function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text)-1});
        if ($pad > strlen($text))
        {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
        {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }

    /**
     * RSA加签
     * @param $paramStr
     * @param $priKey
     * @return string
     */
    public static function rsaSign($paramStr, $priKey){
        $dataGBK = iconv('UTF-8', 'GBK', $paramStr);
        $sign = '';
        //将字符串格式公私钥转为pem格式公私钥
        $priKeyPem = self::format_secret_key($priKey, 'pri');
        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
        $res = openssl_get_privatekey($priKeyPem);
        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($dataGBK, $sign, $res,OPENSSL_ALGO_MD5);
        //释放资源
        openssl_free_key($res);
        //base64编码签名
        $signBase64 = base64_encode($sign);
        //url编码签名
        // $signs = urlencode($signBase64);
        return  $signBase64;
    }

    /**
     * RSA验签
     * @param $paramStr
     * @param $sign
     * @param $pubKey
     * @return bool
     */
    public static function rsaVerify($paramStr, $sign, $pubKey)  {
        $dataGBK = iconv('UTF-8', 'GBK', $paramStr);
        //将字符串格式公私钥转为pem格式公私钥
        $pubKeyPem = self::format_secret_key($pubKey, 'pub');
        //转换为openssl密钥，必须是没有经过pkcs8转换的公钥
        $res = openssl_get_publickey($pubKeyPem);
        //url解码签名
        //$signUrl = urldecode($sign);
        //base64解码签名
        $signBase64 = base64_decode($sign);
        //调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($dataGBK, $signBase64, $res,OPENSSL_ALGO_MD5);
        //释放资源
        openssl_free_key($res);
        //返回资源是否成功
        return $result;
    }

    /**
     * 将字符串格式公私钥格式化为pem格式公私钥
     * @param $secret_key
     * @param $type
     * @return string
     */
    public static function format_secret_key($secret_key, $type){
        //64个英文字符后接换行符"\n",最后再接换行符"\n"
        $key = (wordwrap($secret_key, 64, "\n", true))."\n";
        //添加pem格式头和尾
        if ($type == 'pub') {
            $pem_key = "-----BEGIN PUBLIC KEY-----\n" . $key . "-----END PUBLIC KEY-----\n";
        }else if ($type == 'pri') {
            $pem_key = "-----BEGIN RSA PRIVATE KEY-----\n" . $key . "-----END RSA PRIVATE KEY-----\n";
        }else{
            echo('公私钥类型非法');
            exit();
        }
        return $pem_key;
    }
}
