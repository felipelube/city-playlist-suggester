<?php

namespace App\Services;

use App\Services\Normalizers\CityNameNormalizer;
use Cmfcmf\OpenWeatherMap;
use Cmfcmf\OpenWeatherMap\NotFoundException;
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
        if (!is_string($cityName)) {
            throw new \InvalidArgumentException('getTemperatureFromCityByName: tipo de valor inválido para cityName');
        }
        if (empty($cityName)) {
            throw new \InvalidArgumentException('getTemperatureFromCityByName: informe o nome da cidade');
        }
        /*
            FIXME: de quem é a resposabilidade de encapsular as exceções da biblioteca? Esta classe ou o controller?
            RESPOSTA: é o controller que deverá lançar outras exceções, pois este código pode ser usado em outro contexto
            como um comando numa CLI. Idealmente, eu deveria criar minhas própriax exceções customizadas para este
            serviço.
        */

        try {
            //FIXME: este normalizador deve ser utilizado no controller
            $normalizedName = CityNameNormalizer::normalize($cityName);

            return $this->cache->get("city-$normalizedName", function (ItemInterface $item) use ($normalizedName) {
                $weather = $this->owm->getWeather($normalizedName, 'metric', 'pt_br'); //TODO: parametrizar?
                $item->expiresAfter(600); //TODO: parametrizar

                return $weather->temperature->now->getValue();
                //TODO: uma entrada com as coordenadas da cidade deve ser criada também?
            });
        } catch (NotFoundException $e) {
            throw new HttpException(404, 'Cidade não encontrada', $e);
        } catch (\InvalidArgumentException $e) {
            throw new HttpException(400, "Parâmetros inválidos:\n{$e->getMessage()}", $e);
        }
    }

    public function getTemperatureFromCityByLocation($latitude, $longitude)
    {
        // TODO: usar um cache
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
            $weather = $this->owm->getWeather(['lat' => $latitude, 'lon' => $longitude], 'metric', 'pt_br');
        } catch (\InvalidArgumentException $e) {
            throw new HttpException(400, "Parâmetros inválidos:\n{$e->getMessage()}", $e);
        }

        return $weather->temperature->now->getValue();
    }
}
