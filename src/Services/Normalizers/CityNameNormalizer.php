<?php

namespace App\Services\Normalizers;

/**
 * Este normalizador remove os acentos de uma string e deixa ela com caixa baixa.
 */
class CityNameNormalizer
{
    public static function normalize($name)
    {
        return \transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $name);
    }
}
