<?php

namespace App\Tests;

use App\Exception\InvalidInputException;
use App\Services\LocationTemperatureGetter;
use Cmfcmf\OpenWeatherMap\Tests\TestHttpClient;
use Http\Factory\Guzzle\RequestFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class LocationTemperatureGetterTest extends KernelTestCase
{
    /**
     * @var LocationTemperatureGetter
     */
    protected $service;

    protected function setUp(): void
    {
        /*
         * Já que queremos testar apenas o processamento de getTemperatureFromCityByName,
         * podemos instanciar o serviço diretamente aqui com um cliente http fake utilizado
         * pela biblioteca OpenWeatherMap em seus testes, de forma a evitar requisições
         * verdadeiras à API.
         */
        $this->service = new LocationTemperatureGetter('teste', new TestHttpClient(), new RequestFactory(), new ArrayAdapter());
    }

    public function testGetTemperatureFromCityByNameWithEmptyString()
    {
        $this->expectException(InvalidInputException::class);
        $this->service->getTemperatureFromCityByName('');
    }

    public function testGetTemperatureFromCityByNameWithWrongType()
    {
        $this->expectException(InvalidInputException::class);
        $this->service->getTemperatureFromCityByName(0);
    }

    public function testGetTemperatureFromCityByNameReturnType()
    {
        $temperature = $this->service->getTemperatureFromCityByName('teste');
        $this->assertTrue(\is_float($temperature));
    }

    public function testGetTemperatureFromCityByLocationWithUnsupportedFormat()
    {
        $this->expectException(InvalidInputException::class);
        $this->service->getTemperatureFromCityByLocation('41 25 01N', '120 58 57W');
    }

    public function testGetTemperatureFromCityByLocationInvalidLatitudeValue()
    {
        $this->expectException(InvalidInputException::class);
        $this->service->getTemperatureFromCityByLocation('-90.1', '10');
    }

    public function testGetTemperatureFromCityByLocationInvalidLongitudeValue()
    {
        $this->expectException(InvalidInputException::class);
        $this->service->getTemperatureFromCityByLocation(0, 180.2);
    }

    public function testGetTemperatureFromCityByLocationReturnType()
    {
        $temperature = $this->service->getTemperatureFromCityByLocation('20.2976', '40.2958');
        $this->assertTrue(\is_float($temperature));
    }

    public function testInvalidCreation() {
        $this->expectException(InvalidInputException::class);
        $mockClient = $this->createMock(\Psr\Http\Client\ClientInterface::class);
        $mockFactory = $this->createMock(\Psr\Http\Message\RequestFactoryInterface::class);
        new LocationTemperatureGetter("", $mockClient, $mockFactory, new ArrayAdapter());
    }

    public function testCityNotFound() {
        $this->markTestIncomplete(
            'Teste não implementado ainda.'
        );
    }

    /**
     * Testa se repetidas solicitações para a mesma cidade são servidas pelo
     * cache a partir da primeira computação.
     */
    public function testCachedResponseForCity() {
        $this->markTestIncomplete(
            'Teste não implementado ainda.'
        );
    }


    /**
     * Testa se repetidas solicitações para a mesma localização geográfica são servidas pelo
     * cache a partir da primeira computação.
     */
    public function testCachedResponseForSameGeographicCoordinates() {
        $this->markTestIncomplete(
            'Teste não implementado ainda.'
        );
    }

    /**
     * Testa se repetidas solicitações para a mesma localização geográfica são servidas pelo
     * cache a partir da primeira computação.
     * Verifica se mudanças infinitesimais nas coordenadas NÃO causam recomputação das mesmas.
     */
    public function testCachedResponseForSmallChangesInTheSameGeographicCoordinates() {
        $this->markTestIncomplete(
            'Teste não implementado ainda.'
        );
    }


}
