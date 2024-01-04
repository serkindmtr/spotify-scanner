<?php

declare(strict_types=1);

namespace src\dto;

use DateTime;

final readonly class SongDto
{
    public function __construct(private string $artist, private string $name)
    {
    }

    public function getArtist(): string
    {
        return $this->artist;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
