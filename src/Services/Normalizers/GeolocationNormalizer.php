<?php

namespace App\Services\Normalizers;

/**
 * Este normalizador arrendonda o valor das coordenadas geográficas até uma
 * quantidade de casas decimais determinada.
 */
class GeolocationNormalizer
{
    const PRECISION = 1;

    public static function normalize($latitude, $longitude)
    {
        return [
            \round($latitude, self::PRECISION),
            \round($longitude, self::PRECISION),
        ];
    }
}
