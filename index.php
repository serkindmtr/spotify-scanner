<?php

//https://open.spotify.com/playlist/7bzNsBT3n9L0mwa7kweb5N?si=23a1e3da99e04601
//  TODO: 
//  1) выводить в файл (csv?)
//  2) на вход принимать ссылку на плейлист и парсить по ссылке
//  3) 

ini_set('memory_limit','2G');

function get_token(): string {
    // Инициализация сеанса cURL
    $ch = curl_init();
    // Установка URL
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

    // Установка CURLOPT_RETURNTRANSFER (вернуть ответ в виде строки)
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Выполнение запроса cURL
    //$output содержит полученную строку
    $accessToken = json_decode(curl_exec($ch), true)['accessToken'];

    var_dump($accessToken);

    // закрытие сеанса curl для освобождения системных ресурсов
    curl_close($ch);

    return $accessToken;
}

function get_part_of_list(string $accessToken, int $offset): string {
    // Инициализация сеанса cURL
    $ch = curl_init();
    // Установка URL
    curl_setopt(
        handle: $ch, 
        option: CURLOPT_URL, 
        value: "https://api-partner.spotify.com/pathfinder/v1/query?operationName=fetchPlaylist&variables=%7B%22uri%22%3A%22spotify%3Aplaylist%3A7bzNsBT3n9L0mwa7kweb5N%22%2C%22offset%22%3A" . 
        (string) $offset .
        "%2C%22limit%22%3A25%7D&extensions=%7B%22persistedQuery%22%3A%7B%22version%22%3A1%2C%22sha256Hash%22%3A%22b39f62e9b566aa849b1780927de1450f47e02c54abf1e66e513f96e849591e41%22%7D%7D"
    );
    // Установка CURLOPT_RETURNTRANSFER (вернуть ответ в виде строки)
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

    $headers = [];
    $headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/115.0';
    $headers[] = 'Accept: application/json';
    $headers[] = 'Accept-Language: ru';
    $headers[] = 'Accept-Encoding: gzip, deflate, br';
    $headers[] = 'Referer: https://open.spotify.com/';
    $headers[] = 'Authorization: Bearer ' . $accessToken ;
    $headers[] = 'App-Platform: WebPlayer';
    $headers[] = 'Spotify-App-Version: 1.2.18.423.g700a9490';
    $headers[] = 'Content-Type: application/json;charset=UTF-8';
    $headers[] = 'client-token: AAAFNdsdGunBieVnkDqmSIoFYhxu+P2xITDetjIYD2n6XBAJqnVHf8sU8yqNzoQlYqzsDP6kQ6siw9vl5x+uPv2ie1phhrzDBVKbtvw2QLd1s+875ir/lAFiy9FGaQs7jV+mqpqNClqaQH5F/PjYU4BNE1LWB6OL6+KzsGJIHlsa5LQXMCgNhAoqU2bKALhnqLHTOsw9nVd/wSiaq4nz/SXfOlCB/2zH0uAcg4sy/7ZEpqpU2XxmJfZeNRzcm07moGn2RMIA4tcoeTvaW3kUC+iEbRMJd6xm8fM4T7NZm6xVOuyIAoZbD1V71BiGYMjwb4pLqhQ=';
    $headers[] = 'Origin: https://open.spotify.com';
    $headers[] = 'DNT: 1';
    $headers[] = 'Connection: keep-alive';
    $headers[] = 'Sec-Fetch-Dest: empty';
    $headers[] = 'Sec-Fetch-Mode: cors';
    $headers[] = 'Sec-Fetch-Site: same-site';
    $headers[] = 'TE: trailers';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $execResult = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    return $execResult;
}

function print_result(array $result): array {
    $countPrintedSongs = 0;

    $result = $result['data']['playlistV2'];
    print('Playlist name: ' . $result['name'] . "\n");
    print('Count os songs: '. $result['content']['totalCount'] . "\n");
    
    foreach($result['content']['items'] as $item) {
        $artistsNames = '';
        foreach ($item['itemV2']['data']['artists']['items'] as $artist) {
            $artistsNames .= $artist['profile']['name'] . ' ';
        }

        $songName = $item['itemV2']['data']['name'];
        print($artistsNames . ' – ' . $songName . "\n");
        $countPrintedSongs++;
    }

    return [$countPrintedSongs, (int)$result['content']['totalCount']];
}

$accessToken = get_token();
$offset = 0;
$execResult = get_part_of_list($accessToken, $offset);
$result = json_decode($execResult, true);
[$countPrinted, $totalCount] = print_result($result);
$offset += $countPrinted;
while ($offset < $totalCount) {
    $execResult = get_part_of_list($accessToken, $offset);
    $result = json_decode($execResult, true);
    [$countPrinted, $totalCount] = print_result($result);
    $offset += $countPrinted;
}
