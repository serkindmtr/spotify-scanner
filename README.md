# spotify-scanner

### What does this code do?
Do you need a list of songs that you have collected on Spotify? Great! Copy the link to your playlist via the share button. Run this script by specifying this link in the parameter `--link=`

### Usage:
```php
php index.php --link={link_to_the_shared_spotify_playlist}
```

### Output Example:
```
Playlist name: {PlaylistName}
Count of songs: {CountOfSongs}
{Artists} - {Song Name}
```