<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

class CityPlaylistController extends PlaylistController
{
    /**
     * @Route("/cidade/{name}", name="city_playlist")
     */
    public function getPlaylistForCity($name)
    {
        $temperature = $this->locationTemperatureGetter->getTemperatureFromCityByName($name);

        return $this->makeResponseFromTemperature($temperature);
    }
}
