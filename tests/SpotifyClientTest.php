<?php

namespace App\Tests;

use App\Services\SpotifyClient;
use AssertionError;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\Exception\TimeoutException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class SpotifyClientTests extends TestCase
{
    const SPOTIFY_VALID_ACCESS_TOKEN_RESPONSES = [[
      'access_token' => 'NgCXRKcqekjwqhewqlkewjqhedsawqeMzYjw',
      'token_type' => 'bearer',
      'expires_in' => 1,
    ], [
      'access_token' => 'asjlfhwklerweprwieujnweflkj',
      'token_type' => 'bearer',
      'expires_in' => 1,
    ]];

    const SPOTIFY_VALID_CATEGORY_RESPONSE = [
      'href' => 'https://api.spotify.com/v1/browse/categories/party',
      'icons' => [[
        'height' => 274,
        'url' => 'https://datsnxq1rwndn.cloudfront.net/media/derived/party-274x274_73d1907a7371c3bb96a288390a96ee27_0_0_274_274.jpg',
        'width' => 274,
       ]],
      'id' => 'party',
      'name' => 'Party',
    ];

    private function makeTimeoutRequest()
    {
        yield '';
    }

    private function makeAuthenticatedRequest($mockedResponses, $usedService = null, $usedCache = null)
    {
        $httpClient = new MockHttpClient($mockedResponses);
        $cache = isset($usedCache)
          ? $usedCache
          : new ArrayAdapter();
        $service = isset($usedService)
          ? $usedService
          : new SpotifyClient('client_id', 'client_secret', $httpClient, $cache);
        $response = $service->authenticatedRequest('GET', 'http://example.com', []);

        return [$service, $cache, $response];
    }

    public function testTokenIsProducedAndInCache()
    {
        list($service, $cache) = $this->makeAuthenticatedRequest([
          new MockResponse(json_encode(self::SPOTIFY_VALID_ACCESS_TOKEN_RESPONSES[0]), []),
          new MockResponse(json_encode(self::SPOTIFY_VALID_CATEGORY_RESPONSE), []),
        ]);

        $this->assertEquals(
          self::SPOTIFY_VALID_ACCESS_TOKEN_RESPONSES[0]['access_token'],
          $cache->get($service::CACHE_ACCESS_TOKEN_KEY, function () {
              throw new AssertionError('O item não deveria ser recalculado neste ponto.');
          })
        );
    }

    public function testIfTokenIsRefreshedAfterExpiration()
    {
        $this->markTestIncomplete(
          'Teste não implementado ainda.'
        );
    }

    public function testIfARequestTimeoutMakesTheClientRetry()
    {
        list($service, $cache, $response) = $this->makeAuthenticatedRequest([
          new MockResponse($this->makeTimeoutRequest()),
          new MockResponse(json_encode(self::SPOTIFY_VALID_ACCESS_TOKEN_RESPONSES[0])),
          new MockResponse(json_encode(self::SPOTIFY_VALID_CATEGORY_RESPONSE)),
        ]);
        $this->assertEquals(
          self::SPOTIFY_VALID_ACCESS_TOKEN_RESPONSES[0]['access_token'],
          $cache->get($service::CACHE_ACCESS_TOKEN_KEY, function () {
              throw new AssertionError('O item não deveria ser recalculado neste ponto.');
          })
        );
        $this->assertEquals(
          self::SPOTIFY_VALID_CATEGORY_RESPONSE,
          $response->toArray()
        );
    }

    public function testIfAHttpErrorMakesTheClientRetry()
    {
        $this->markTestIncomplete(
          'Teste não implementado ainda.'
        );
    }

    public function testRequestRetryBackoffWillEventuallyThrowException()
    {
        $this->expectException(TimeoutException::class);
        $requests = [
          new MockResponse($this->makeTimeoutRequest()),
          new MockResponse($this->makeTimeoutRequest()),
          new MockResponse($this->makeTimeoutRequest()),
          new MockResponse($this->makeTimeoutRequest()),
          new MockResponse($this->makeTimeoutRequest()),
        ];
        list($service, $cache, $response) = $this->makeAuthenticatedRequest($requests);
    }
}
