<?php

declare(strict_types=1);

namespace src\dto;

use DateTime;

final class PlaylistDto
{
    /** @var SongDto[] */
    private array $songs = [];

    public function __construct(private readonly string $name, private int $countSongs)
    {
    }

    public function addSong(string $artist, string $name): void
    {
        $this->songs[] = new SongDto($artist, $name, new DateTime());
    }

    public function getCountSongs(): int
    {
        return $this->countSongs;
    }

    public function setCountSongs(int $count): void
    {
        $this->countSongs = $count;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return SongDto[]
     */
    public function getSongs(): array
    {
        return $this->songs;
    }
}
