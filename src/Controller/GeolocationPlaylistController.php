<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GeolocationPlaylistController extends PlaylistController
{
    /**
     * @Route("/localizacao", name="location_playlist")
     */
    public function getPlaylistForLocation(Request $request)
    {
        $latitude = $request->query->get('lat');
        $longitude = $request->query->get('lon');
        $temperature = $this->locationTemperatureGetter->getTemperatureFromCityByLocation($latitude, $longitude);

        return $this->makeResponseFromTemperature($temperature);
    }
}
