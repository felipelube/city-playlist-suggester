<?php

namespace App\Controller;

use App\Services\LocationTemperatureGetter;
use App\Services\MusicGenreChooser;
use App\Services\SpotifyPlaylistGetter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class PlaylistController extends AbstractController
{
    /**
     * @var MusicGenreChooser
     */
    protected $musicGenreChooser;

    /**
     * @var LocationTemperatureGetter
     */
    protected $locationTemperatureGetter;

    /**
     * @var SpotifyPlaylistGetter
     */
    protected $playlistGetter;

    public function __construct(
        LocationTemperatureGetter $locationTemperatureGetter,
        MusicGenreChooser $musicGenreChooser,
        SpotifyPlaylistGetter $playlistGetter
    ) {
        $this->locationTemperatureGetter = $locationTemperatureGetter;
        $this->musicGenreChooser = $musicGenreChooser;
        $this->playlistGetter = $playlistGetter;
    }

    final protected function makeResponseFromTemperature($temperature)
    {
        $genre = $this->musicGenreChooser::chooseGenreFromTemperature($temperature);
        $playlist = $this->playlistGetter->getPlaylistForGenre($genre);

        return $this->json([
            'temperatura' => $temperature,
            'gênero musical' => $genre,
            'playlist' => $playlist,
        ]);
    }
}
