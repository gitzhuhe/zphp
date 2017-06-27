<?php
namespace ZPHP\Dora;

use ZPHP\Core\Config;
use ZPHP\Dora\DoraConst;

class Packet
{

    public static function packFormat($guid, $msg = "OK", $code = 0, $data = array())
    {
        $pack = array(
            "guid" => $guid,
            "code" => $code,
            "msg" => $msg,
            "result" => $data,
        );

        return $pack;
    }

    public static function packEncode($data, $type = "tcp")
    {

        if ($type == "tcp") {
            $guid = $data["guid"];
            $sendStr = serialize($data);

            //if compress the packet
            if (Config::getField('tcp','SW_DATACOMPRESS_FLAG',false) == true) {
                $sendStr = gzencode($sendStr, 4);
            }

            if (Config::getField('tcp','SW_DATASIGEN_FLAG',false) == true) {
                $signedcode = pack('N', crc32($sendStr . Config::getField('tcp','SW_DATASIGEN_SALT','')));
                $sendStr = pack('N', strlen($sendStr) + 4 + 32) . $signedcode . $guid . $sendStr;
            } else {
                $sendStr = pack('N', strlen($sendStr) + 32) . $guid . $sendStr;
            }

            return $sendStr;
        } else if ($type == "http") {
            $sendStr = json_encode($data);
            return $sendStr;
        } else {
            return self::packFormat($data["guid"], "packet type wrong", 100006);
        }

    }

    public static function packDecode($str)
    {
        $header = substr($str, 0, 4);
        $len = unpack("Nlen", $header);
        $len = $len["len"];

        if (Config::getField('tcp','SW_DATASIGEN_FLAG',false) == true) {

            $signedcode = substr($str, 4, 4);
            $guid = substr($str, 8, 32);
            $result = substr($str, 40);

            //check signed
            if (pack("N", crc32($result . Config::getField('tcp','SW_DATASIGEN_SALT',''))) != $signedcode) {
                throw new \Exception(500);
                //return self::packFormat($guid, "Signed check error!", 100005);
            }

            $len = $len - 4 - 32;

        } else {
            $guid = substr($str, 4, 32);
            $result = substr($str, 36);
            $len = $len - 32;
        }
        if ($len != strlen($result)) {
            //结果长度不对
            //return self::packFormat($guid, "packet length invalid 包长度非法", 100007);
            throw new \Exception(500);
        }
        //if compress the packet
        if (Config::getField('tcp','SW_DATACOMPRESS_FLAG',false) == true) {
            $result = gzdecode($result);
        }
        $result = unserialize($result);

        return self::packFormat($guid, "OK", 0, $result);
    }
}
