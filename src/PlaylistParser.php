<?php

declare(strict_types=1);

namespace src;

use src\dto\PlaylistDto;

final class PlaylistParser
{
    private int $offset = 0;
    private int $limit = 25;
    private array $urlVariable;
    private bool $isPlaylistParsed = false;
    private ?PlaylistDto $playlist = null;

    private array $urlExtensions = [
        "persistedQuery" => [
            "version" => 1,
            "sha256Hash" => "b39f62e9b566aa849b1780927de1450f47e02c54abf1e66e513f96e849591e41"
        ]
    ];

    public function __construct(
        private readonly string $authToken,
        private readonly string $clientToken,
        private readonly string $playlistId
    ) {
        $this->urlVariable = [
            "uri" => "spotify:playlist:" . $this->playlistId,
            "offset" => $this->offset,
            "limit" => $this->limit
        ];
    }

    public function isPlaylistParsed(): bool
    {
        return $this->isPlaylistParsed;
    }

    public function parse(): void
    {
        $info = $this->getPartOfPlaylistInfo();
        if (is_null($this->playlist)) {
            $this->playlist = new PlaylistDto(
                name: $info['data']['playlistV2']['name'],
                countSongs: (int)$info['data']['playlistV2']['content']['totalCount']
            );
        }

        if ((int)$info['data']['playlistV2']['content']['totalCount'] !== $this->playlist->getCountSongs()) {
            echo 'Changed count songs in playlist';
            $this->playlist->setCountSongs(count: (int)$info['data']['playlistV2']['content']['totalCount']);
        }

        foreach ($info['data']['playlistV2']['content']['items'] as $item) {
            $artistsNames = '';
            foreach ($item['itemV2']['data']['artists']['items'] as $artist) {
                $artistsNames .= $artist['profile']['name'] . ' ';
            }
            $songName = $item['itemV2']['data']['name'];

            $this->playlist->addSong(artist: $artistsNames, name: $songName);
        }

        if ($this->offset >= $this->playlist->getCountSongs()) {
            $this->isPlaylistParsed = true;
        }

        $this->urlVariable['offset'] += $this->limit;
    }

    private function getPartOfPlaylistInfo(): array
    {
        $ch = curl_init();
        curl_setopt(
            handle: $ch,
            option: CURLOPT_URL,
            value: "https://api-partner.spotify.com/pathfinder/v1/query?" .
            "operationName=fetchPlaylist&" .
            "variables=" . rawurlencode(json_encode($this->urlVariable)) . "&" .
            "extensions=" . rawurlencode(json_encode($this->urlExtensions))
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = [];
        $headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/115.0';
        $headers[] = 'Accept: application/json';
        $headers[] = 'Accept-Language: ru';
        $headers[] = 'Accept-Encoding: gzip, deflate, br';
        $headers[] = 'Referer: https://open.spotify.com/';
        $headers[] = 'Authorization: Bearer ' . $this->authToken;
        $headers[] = 'App-Platform: WebPlayer';
        $headers[] = 'Spotify-App-Version: 1.2.18.423.g700a9490';
        $headers[] = 'Content-Type: application/json;charset=UTF-8';
        $headers[] = 'client-token: ' . $this->clientToken;
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

        $this->offset += $this->limit;

        return json_decode($execResult, true);
    }

    public function getPlaylist(): PlaylistDto
    {
        return $this->playlist;
    }
}
