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
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Serviço para busca de temperatura utilizando a API do OpenWeatherMap (https://openweathermap.org/api).
 * Caracterísicas:
 * - Permite a busca por nome de cidade e coordenadas geográficas.
 * - Utiliza a biblioteca Cmfcmf\OpenWeatherMap para fazer as requisições.
 * - Utilização de cache para evitar o uso excessivo da API.
 */
class LocationTemperatureGetter
{
    /**
     * O cliente da API OpenWeatherMap.
     *
     * @var OpenWeatherMap
     */
    private $owm;

    /**
     * Uma implementação de um adaptador de contrato de cache do Symfony.
     *
     * @var AdapterInterface
     */
    private $cache;

    /**
     * Constrói uma instância do serviço LocationTemperatureGetter.
     *
     * @param string                  $owmAppId       ID de aplicativo (API Key) necessário para utilização da API
     * @param ClientInterface         $httpClient     Uma implementação de cliente HTTP (PSR-18)
     * @param RequestFactoryInterface $requestFactory Uma implementação de fábrica de requisições (PSR-17)
     * @param AdapterInterface        $cache          Um adaptador de cache do Symfony
     *
     * @throws InvalidInputException
     */
    public function __construct(
        string $owmAppId,
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        AdapterInterface $cache
    ) {
        try {
            $this->owm = new OpenWeatherMap($owmAppId, $httpClient, $requestFactory);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidInputException('LocationTemperatureGetter: falha na criação do cliente OpenWeather. Você passou uma chave de API válida?', 400, $e);
        }
        $this->cache = $cache;
    }

    /**
     * Pega a temperatura de uma cidade pelo seu nome.
     *
     * @param string $cityName O nome da cidade
     *
     * @return float A temperatura em graus celsius
     *
     * @throws InvalidInputException
     * @throws NotFoundException
     */
    public function getTemperatureFromCityByName(string $cityName)
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

    /**
     * Pega a temperatura de uma cidade pela sua localização geográfica.
     * Retorna uma temperatura já foi calculada do cache; caso não haja cache, busca essa informação na API
     * do OpenWeatherMap, salva ela no cache para pesquisas rápidos no futuro e retorna a temperatura.
     *
     * @param string $cityName O nome da cidade
     *
     * @return float A temperatura em graus celsius
     *
     * @throws InvalidInputException
     */
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
