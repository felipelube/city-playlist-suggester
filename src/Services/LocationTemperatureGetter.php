<?php

namespace App\Services;
use Cmfcmf\OpenWeatherMap;
use Cmfcmf\OpenWeatherMap\Exception as CmfcmfException;
use Cmfcmf\OpenWeatherMap\NotFoundException;
use PhpParser\Node\Expr\Instanceof_;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;


class LocationTemperatureGetter {
  /**
   * @var OpenWeatherMap
   */
  private $owm;

  public function __construct($owmAppId, ClientInterface $httpClient, RequestFactoryInterface $requestFactory) {
    $this->owm = new OpenWeatherMap($owmAppId, $httpClient, $requestFactory);
  }
  /**
       * Encapsule esta exceção lançada pela biblioteca
       * numa HttpException, que poderá ser tratada pelo
       * Symfony mais acima.
      */
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
    //FIXME: de quem é a resposabilidade de encapsular as exceções da biblioteca? Esta classe ou o controller?
    try {
      $weather = $this->owm->getWeather($cityName, "metric", "pt_br");
      return $weather->temperature->now->getValue();
    } catch(NotFoundException $e) {
      throw new HttpException(404, "Cidade não encontrada", $e);
    } catch(\InvalidArgumentException $e)  {
      throw new HttpException(400, "Parâmetros inválidos:\n{$e->getMessage()}", $e);
    }

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