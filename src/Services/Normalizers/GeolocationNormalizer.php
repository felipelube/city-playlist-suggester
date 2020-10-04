<?php

namespace App\Services\Normalizers;

use App\Exception\InvalidInputException;

// TODO: refatorar para algo genérico

/**
 * Este normalizador arrendonda o valor das coordenadas geográficas até uma
 * quantidade de casas decimais determinada.
 */
class GeolocationNormalizer
{
    const PRECISION = 1;

    /**
     * Função estática para normalizar coordenadas geográficas.
     *
     * @param int|float $latitude  A latitude
     * @param int|float $longitude A longitude
     *
     * @return Array[float]
     */
    public static function normalize($latitude, $longitude)
    {
        if (!is_numeric($latitude) || empty($latitude)) {
            throw new InvalidInputException('Latitude inválida. Utilize um valor numérico para a latitude');
        }

        if (!is_numeric($longitude) || empty($longitude)) {
            throw new InvalidInputException('Longitude inválida. Utilize um valor numérico para a longitude');
        }

        return [
            \round($latitude, self::PRECISION),
            \round($longitude, self::PRECISION),
        ];
    }
}
