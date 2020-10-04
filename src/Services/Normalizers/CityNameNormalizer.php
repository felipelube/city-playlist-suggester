<?php

namespace App\Services\Normalizers;

/**
 * Este normalizador remove os acentos de uma string e deixa ela com caixa baixa.
 */
class CityNameNormalizer
{
    const TRANSLITERATOR = 'Any-Latin; Latin-ASCII; Lower()';

    public static function normalize($name)
    {
        return \transliterator_transliterate(self::TRANSLITERATOR, $name);
    }
}
