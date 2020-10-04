<?php

namespace App\Controller;

use App\Exception\InvalidInputException;
use App\Exception\NotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class CityPlaylistController extends PlaylistController
{
    /**
     * @Route("/cidade/{name}", name="city_playlist")
     */
    public function getPlaylistForCity($name)
    {
        try {
            $temperature = $this->locationTemperatureGetter->getTemperatureFromCityByName($name);
        } catch (NotFoundException $e) {
            throw new HttpException(404, 'Cidade não encontrada', $e);
        } catch (InvalidInputException $e) {
            throw new HttpException(400, 'Nome de cidade inválido', $e);
        }

        return $this->makeResponseFromTemperature($temperature);
    }
}
