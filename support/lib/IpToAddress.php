<?php
/**
 * Created by PhpStorm.
 * User: 小灰灰
 * Date: 2022-06-24
 * Time: 9:14:43
 * Info:
 */

namespace support\lib;

use itbdw\Ip\IpLocation;

class IpToAddress {

    /**
     * ip地址转换为地区名
     */
    public function ipToAddress($clientIP = '') {
        //优先使用纯真ip库解析
        if ($clientIP == '127.0.0.1') {
            return ['country' => '', 'province' => '', 'city' => '', 'isp' => ''];
        }
        $address = $this->convertIp($clientIP);

        return $address;
    }

    /**
     * 使用纯真数据库插件调用函数
     */
    public function convertIp($clientIP) {
        $res = IpLocation::getLocation($clientIP);
        //使用 speedtest 解析
        if (empty($res['province']) || empty($res['city'])) {
            $res = $this->speedtest($clientIP);
        }

        return $res;
    }

    /**
     * speedtest
     */
    public function speedtest($clientIP) {
        $res = [];

        $content = $this->curl_get("https://forge.speedtest.cn/api/location/info?ip={$clientIP}");
        $contentArr = json_decode($content, true);

        $res['country'] = !empty($contentArr['country']) ? $contentArr['country'] : '';
        $res['province'] = !empty($contentArr['province']) ? $contentArr['province'] : '';
        $res['city'] = !empty($contentArr['city']) ? $contentArr['city'] : '';
        $res['isp'] = !empty($contentArr['isp']) ? $contentArr['isp'] : '';
        //使用 ip_cn 解析
        if (empty($res['province']) || empty($res['city'])) {
            $res = $this->ip_cn($clientIP);
        }

        return $res;
    }

    /**
     * ip.cn
     */
    public function ip_cn($clientIP) {
        $res = ['country' => '', 'province' => '', 'city' => '', 'isp' => ''];

        $content = $this->curl_get("https://ip.cn/api/index?ip={$clientIP}&type=1");
        $contentArr = json_decode($content, true);
        if (empty($contentArr['address'])) {
            return $res;
        }
        $address = explode(' ', $contentArr['address']);
        $address = array_values(array_filter($address));
        $res['country'] = !empty($address[0]) ? $address[0] : '';
        $res['province'] = !empty($address[1]) ? $address[1] : '';
        $res['city'] = !empty($address[2]) ? $address[2] : '';
        $res['isp'] = !empty($address[3]) ? $address[3] : '';

        //使用 cz88.net 解析
        if (empty($res['province']) || empty($res['city'])) {
            $res = $this->cz88_net($clientIP);
        }

        return $res;

    }

    /**
     * cz88.net
     */
    public function cz88_net($clientIP) {
        $res = ['country' => '', 'province' => '', 'city' => '', 'isp' => ''];

        $content = $this->curl_get("https://www.cz88.net/api/cz88/ip/iplab?ip={$clientIP}");
        $contentArr = json_decode($content, true);
        if (empty($contentArr) || $contentArr['code'] != '200') {
            return $res;
        }
        if (empty($contentArr['data'])) {
            return $res;
        }
        $data = $contentArr['data'];
        $netAddress = explode('-', $data['netAddress']);
        $res['country'] = !empty($netAddress[0]) ? $netAddress[0] : '';
        $res['province'] = !empty($netAddress[1]) ? $netAddress[1] : '';
        $res['city'] = !empty($netAddress[2]) ? $netAddress[2] : '';
        $res['isp'] = !empty($data['isp']) ? $data['isp'] : '';

        if (empty($res['province']) || empty($res['city'])) {
            $res = $this->ip_api($clientIP);
        }

        return $res;
    }

    /**
     * http://ip-api.com/
     */
    public function ip_api($clientIP) {
        $res = ['country' => '', 'province' => '', 'city' => '', 'isp' => ''];

        $content = $this->curl_get("http://ip-api.com/json/{$clientIP}?lang=zh-CN");
        $contentArr = json_decode($content, true);
        if (empty($contentArr)) {
            return $res;
        }
        if (!empty($contentArr['status']) && $contentArr['status'] != 'success') {
            return $res;
        }
        $res['country'] = !empty($contentArr['country']) ? $contentArr['country'] : '';
        $res['province'] = !empty($contentArr['regionName']) ? $contentArr['regionName'] : '';
        $res['city'] = !empty($contentArr['city']) ? $contentArr['city'] : '';
        $res['isp'] = !empty($contentArr['isp']) ? $contentArr['isp'] : '';

        return $res;
    }

    private function curl_get($url) {
        $ch = curl_init();
        $header[] = "";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_USERAGENT,
            "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36");
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $content = curl_exec($ch);
        if ($content === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("cURL request failed: $error");
        }
        curl_close($ch);

        return $content;
    }

}
