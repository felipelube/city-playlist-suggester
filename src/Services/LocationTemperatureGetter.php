<?php

namespace App\Services;

use App\Exception\InvalidInputException;
use App\Exception\NotFoundException;
use App\Services\Normalizers\CityNameNormalizer;
use Cmfcmf\OpenWeatherMap;
use Cmfcmf\OpenWeatherMap\NotFoundException as OWMNotFoundException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Cache\ItemInterface;

class LocationTemperatureGetter
{
    /**
     * @var OpenWeatherMap
     */
    private $owm;

    /**
     * @var AdapterInterface
     */
    private $cache;

    public function __construct($owmAppId, ClientInterface $httpClient, RequestFactoryInterface $requestFactory, AdapterInterface $cache)
    {
        $this->owm = new OpenWeatherMap($owmAppId, $httpClient, $requestFactory);
        $this->cache = $cache;
    }

    /**
     * Encapsule esta exceção lançada pela biblioteca
     * numa HttpException, que poderá ser tratada pelo
     * Symfony mais acima.
     */
    public function getTemperatureFromCityByName($cityName)
    {
        try {
            if (!is_string($cityName)) {
                throw new \InvalidArgumentException('getTemperatureFromCityByName: tipo de valor inválido para cityName');
            }
            if (empty($cityName)) {
                throw new \InvalidArgumentException('getTemperatureFromCityByName: informe o nome da cidade');
            }

            //FIXME: este normalizador deve ser utilizado no controller
            $normalizedName = CityNameNormalizer::normalize($cityName);

            return $this->cache->get("city-$normalizedName", function (ItemInterface $item) use ($normalizedName) {
                $weather = $this->owm->getWeather($normalizedName, 'metric', 'pt_br'); //TODO: parametrizar?
                $item->expiresAfter(600); //TODO: parametrizar

                return $weather->temperature->now->getValue();
                //TODO: uma entrada com as coordenadas da cidade deve ser criada também?
            });
        } catch (OWMNotFoundException $e) {
            throw new NotFoundException($e->getMessage(), 404, $e);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidInputException($e->getMessage(), 400, $e);
        }
    }

    public function getTemperatureFromCityByLocation($latitude, $longitude)
    {
        try {
            if (!is_numeric($latitude) || !is_numeric($longitude)) {
                throw new \InvalidArgumentException('getTemperatureFromCityByLocation: informe valores numéricos para latitude e longitude em graus decimais');
            } else {
                // Verifique se os valores estão dentro dos limites para latitude e longitude
                if ($latitude < -90 || $latitude > 90) {
                    throw new \InvalidArgumentException('getTemperatureFromCityByLocation: valor inválido informado para a latitude');
                }
                if ($longitude < -180 || $longitude > 180) {
                    throw new \InvalidArgumentException('getTemperatureFromCityByLocation: valor inválido informado para a longitude');
                }
            }

            return $this->cache->get("geo-$latitude, $longitude", function (ItemInterface $item) use ($latitude, $longitude) {
                $weather = $this->owm->getWeather(['lat' => $latitude, 'lon' => $longitude], 'metric', 'pt_br');
                $item->expiresAfter(600); //TODO: parametrizar

                return $weather->temperature->now->getValue();
            });
        } catch (\InvalidArgumentException $e) {
            throw new InvalidInputException($e->getMessage(), 400, $e);
        }
    }
}
