<?php

namespace App\Services;
use Cmfcmf\OpenWeatherMap;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;


class LocationTemperatureGetter {
  private $owm;

  public function __construct($owmAppId, ClientInterface $httpClient, RequestFactoryInterface $requestFactory) {
    $this->owm = new OpenWeatherMap($owmAppId, $httpClient, $requestFactory);
  }

  public function getTemperatureFromCityByName($cityName) {
    if (!is_string($cityName)) {
      throw new \InvalidArgumentException(
        "getTemperatureFromCityByName: tipo de valor inválido para cityName"
      );
    }
    if (empty($cityName)) {
      throw new \InvalidArgumentException(
        "getTemperatureFromCityByName: informe o nome da cidade"
      );
    }
    $weather = $this->owm->getWeather($cityName, "metric", "pt_br");
    return $weather->temperature->now->getValue();
  }

  public function getTemperatureFromCityByLocation($latitude, $longitude) {
    if (!is_numeric($latitude) || !is_numeric($longitude)) {
      throw new \InvalidArgumentException(
        "getTemperatureFromCityByLocation: informe valores numéricos para latitude e longitude em graus decimais"
      );
    } else {
      // Verifique se os valores estão dentro dos limites para latitude e longitude
      if ($latitude < -90 || $latitude > 90) {
        throw new \InvalidArgumentException(
          "getTemperatureFromCityByLocation: valor inválido informado para a latitude"
        );
      }
      if ($longitude < -180 || $longitude > 180) {
        throw new \InvalidArgumentException(
          "getTemperatureFromCityByLocation: valor inválido informado para a longitude"
        );
      }
    }
    $weather = $this->owm->getWeather(['lat'=> $latitude, 'lon' => $longitude], "metric", "pt_br");
    return $weather->temperature->now->getValue();
  }
}