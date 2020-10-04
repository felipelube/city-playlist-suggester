<?php

namespace App\Controller;

use App\Services\LocationTemperatureGetter;
use App\Services\MusicGenreChooser;
use App\Services\SpotifyPlaylistGetter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CityPlaylistController extends AbstractController
{
    private $musicGenreChooser;
    private $locationTemperatureGetter;
    private $playlistGetter;

    public function __construct(
        LocationTemperatureGetter $locationTemperatureGetter,
        MusicGenreChooser $musicGenreChooser,
        SpotifyPlaylistGetter $playlistGetter
    ) {
        $this->locationTemperatureGetter = $locationTemperatureGetter;
        $this->musicGenreChooser = $musicGenreChooser;
        $this->playlistGetter = $playlistGetter;
    }

    /**
     * @Route("/cidade/{name}", name="city_playlist")
     */
    public function getPlaylistForCity($name)
    {
        $temperature = $this->locationTemperatureGetter->getTemperatureFromCityByName($name);
        $genre = $this->musicGenreChooser::chooseGenreFromTemperature($temperature);
        $playlist = $this->playlistGetter->getPlaylistForGenre($genre);

        return $this->json([
            'cidade' => $name,
            'temperatura' => $temperature,
            'gênero musical' => $genre,
            'playlist' => $playlist,
        ]);
    }
}
