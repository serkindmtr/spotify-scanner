<?php

declare(strict_types=1);

require_once 'src/Tokenizer.php';
require_once 'src/PlaylistParser.php';
require_once 'src/IOController.php';
require_once 'src/dto/PlaylistDto.php';
require_once 'src/dto/SongDto.php';
require_once 'src/DBController.php';

use src\IOController;
use src\PlaylistParser;
use src\Tokenizer;
use src\DBController;

ini_set('memory_limit', '2G');

$ioController = new IOController();
try {
    $playlistId = $ioController->getPlaylistId();
} catch (Exception $e) {
    print $e->getMessage();
    return;
}

$tokenizer = new Tokenizer();
$offset = 0;
$parser = new PlaylistParser(
    authToken: $tokenizer->getAuthorizationToken(),
    clientToken: $tokenizer->getClientToken(),
    playlistId: $playlistId
);
while (!$parser->isPlaylistParsed()) {
    $parser->parse();
}
$playlist = $parser->getPlaylist();
$db = new DBController();
$db->savePlaylist($playlist);
$ioController->printPlaylist($playlist);
