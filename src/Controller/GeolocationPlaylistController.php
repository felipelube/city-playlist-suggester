<?php

namespace App\Controller;

use App\Services\Normalizers\GeolocationNormalizer;
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
        list($normalizedLatitude, $normalizedLongitude) = GeolocationNormalizer::normalize($latitude, $longitude);
        $temperature = $this->locationTemperatureGetter->getTemperatureFromCityByLocation($normalizedLatitude, $normalizedLongitude);

        return $this->makeResponseFromTemperature($temperature);
    }
}
