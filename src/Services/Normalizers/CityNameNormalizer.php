<?php

namespace App\Services\Normalizers;

use App\Exception\InvalidInputException;

// TODO: refatorar para algo genérico

/**
 * Normalizador que remove os acentos de uma string e deixa ela com caixa baixa.
 */
class CityNameNormalizer
{
    const TRANSLITERATOR = 'Any-Latin; Latin-ASCII; Lower()';

    /**
     * Função estática para normalizar o nome de uma cidade.
     *
     * @param string $name O nome da cidade
     *
     * @return string
     *
     * @throws InvalidInputException
     */
    public static function normalize(string $name)
    {
        if (!is_string($name)) {
            throw new InvalidInputException('CityNameNormalizer: o nome da cidade deve ser uma string em normalize()');
        }

        return \transliterator_transliterate(self::TRANSLITERATOR, $name);
    }
}
