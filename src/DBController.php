<?php

declare(strict_types=1);

namespace src;

use SQLite3;
use src\dto\PlaylistDto;

final class DBController
{
    private const string DATABASE_NAME = 'spotify_scanner.db';
    private SQLite3 $DB;

    public function __construct()
    {
        $this->DB = new SQLite3(dirname(__FILE__) . '/db/' . self::DATABASE_NAME);
        $this->DB->enableExceptions(true);
    }

    public function savePlaylist(PlaylistDto $playlist): void
    {
        $playlistName = $playlist->getName();
        $result = $this->DB->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$playlistName'");

        if (false === $result->fetchArray()) {
            $this->DB->exec("CREATE TABLE $playlistName (artist TEXT, name TEXT, ts INT)");
            $this->DB->exec("CREATE UNIQUE INDEX {$playlistName}_artist_name_idx ON $playlistName (artist, name)");
        }

        $ts = time();
        foreach ($playlist->getSongs() as $song) {
            $insertStatement = $this->DB->prepare(
                "INSERT OR IGNORE INTO $playlistName (artist, name, ts) VALUES (:artist, :name, :ts)"
            );
            $artist = $song->getArtist();
            $insertStatement->bindParam(':artist', $artist);
            $name = $song->getName();
            $insertStatement->bindParam(':name', $name);
            $insertStatement->bindParam(':ts', $ts, SQLITE3_NUM);
            $insertStatement->execute();
        }
    }
}
