<?php

declare(strict_types=1);

namespace src;

use Exception;
use src\dto\PlaylistDto;

/**
 * Input and Output Controller
 */
final class IOController
{
    private const string LINK_ARGUMENT_KEY = '--link=';
    private const string LINK_PREFIX_PATTERN = 'https://open.spotify.com/playlist/';
    private const string LINK_POSTFIX_PATTERN = '?si=';

    /**
     * @throws Exception
     */
    public function getPlaylistId(): string
    {
        if (!isset($_SERVER['argv'][1])) {
            throw new Exception('The link to the playlist is not specified');
        }

        $rawArgument = $_SERVER['argv'][1];
        if (!str_starts_with(haystack: $rawArgument, needle: self::LINK_ARGUMENT_KEY)) {
            throw new Exception('The scanner argument must start with the --link=http://web_link');
        }

        $rawArgument = str_replace(search: self::LINK_ARGUMENT_KEY, replace: '', subject: $rawArgument);
        $rawArgument = str_replace(search: self::LINK_PREFIX_PATTERN, replace: '', subject: $rawArgument);
        $playlistId = strstr(haystack: $rawArgument, needle: self::LINK_POSTFIX_PATTERN, before_needle: true);

        if (!is_string($playlistId) || empty($playlistId)) {
            throw new Exception(
                'The link does not match the pattern: https://open.spotify.com/playlist/{playlist_id}?si={random_id}'
            );
        }

        return $playlistId;
    }

    /**
     * result structure:
     * $result = [
     *  'data' => [
     *      'playlistV2' => [
     *          'name' => 'Playlist name(string)',
     *          'content' => [
     *              'totalCount' => 'Count Songs(int)',
     *              'items' => [
     *                  0 => [
     *                      'itemV2' => [
     *                          'data' => [
     *                              'artists' => [
     *                                  'items' => [
     *                                      0 => [
     *                                          'profile' => [
     *                                              'name' => 'Artists Name(string)'
     *                                          ]
     *                                      ],
     *                                      1 => []
     *                                  ]
     *                              ],
     *                              'name' => 'Songs Name(string)'
     *                          ]
     *                      ]
     *                  ],
     *                  1 => []
     *              ]
     *          ],
     *      ]
     *  ]
     * ];
     */


    public function printPlaylist(PlaylistDto $playlist): void
    {
        print('Playlist name: ' . $playlist->getName() . "\n");
        print('Count of songs: ' . $playlist->getCountSongs() . "\n");

        foreach ($playlist->getSongs() as $song) {
            print($song->getArtist() . ' – ' . $song->getName() . "\n");
        }
    }
}