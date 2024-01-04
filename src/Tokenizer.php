<?php

declare(strict_types=1);

namespace src;

final class Tokenizer
{
    public function getAuthorizationToken(): string
    {
        $ch = curl_init();
        curl_setopt(
            handle: $ch,
            option: CURLOPT_URL,
            value: "https://open.spotify.com/get_access_token?"
        );
        curl_setopt(
            handle: $ch,
            option: CURLOPT_HTTPHEADER,
            value: ['User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/115.0']
        );

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $answer = json_decode(curl_exec($ch), true);
        $accessToken = $answer['accessToken'];

        curl_close($ch);

        return $accessToken;
    }

    public function getClientToken(): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://clienttoken.spotify.com/v1/clienttoken');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            "{\"client_data\":{\"client_version\":\"1.2.28.366.g082c129b\",\"client_id\":\"d8a5ed958d274c2e8ee717e6a4b0971d\",\"js_sdk_data\":{\"device_brand\":\"Apple\",\"device_model\":\"unknown\",\"os\":\"macos\",\"os_version\":\"10.15\",\"device_id\":\"e0f042d2592b9d0ef01c22c4769080f3\",\"device_type\":\"computer\"}}}"
        );
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:121.0) Gecko/20100101 Firefox/121.0';
        $headers[] = 'Accept: application/json';
        $headers[] = 'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3';
        $headers[] = 'Accept-Encoding: gzip, deflate, br';
        $headers[] = 'Referer: https://open.spotify.com/';
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Origin: https://open.spotify.com';
        $headers[] = 'Dnt: 1';
        $headers[] = 'Sec-Gpc: 1';
        $headers[] = 'Connection: keep-alive';
        $headers[] = 'Sec-Fetch-Dest: empty';
        $headers[] = 'Sec-Fetch-Mode: cors';
        $headers[] = 'Sec-Fetch-Site: same-site';
        $headers[] = 'Te: trailers';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = json_decode(curl_exec($ch), true);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return $result['granted_token']['token'];
    }
}
