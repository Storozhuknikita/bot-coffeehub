<?php

/*
 * Класс для работы с Joinposter
 */
class Poster
{

    public static function sendRequest($url, $type = 'get', $params = array(), $json = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if ($type == 'post' || $type == 'put') {
            curl_setopt($ch, CURLOPT_POST, true);

            if ($json) {
                $params = json_encode($params);

                curl_setopt($ch, CURLOPT_HTTPHEADER, "[
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($params)
                        ]");

                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            }
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Poster (http://joinposter.com)');

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    /*
     * Авторизация в Joinposter
     *
     */
    public static function auth($code){

        $data['client_id'] = POSTER_CLIENT_ID;
        $data['client_secret'] = POSTER_CLIENT_SECRET;
        $data['code'] = $code;
        $data['verify'] = md5(implode(':', $data));

        $url = 'https://joinposter.com/api/auth/manage';

        $group = Poster::sendRequest($url, 'post', $data);

        $d = json_decode($group);
        return $d;
    }


}