<?php

namespace App\Services;

use App\Exception\InvalidInputException;

/**
 * Classe com a regra de negócio principal da API.
 */
class MusicGenreChooser
{
    /**
     * Gêneros musicais.
     */
    const PARTY = 'party';
    const POP = 'pop';
    const ROCK = 'rock';
    const CLASSICAL = 'classical';

    /**
     * Retorna um gênero musical de acordo com a temperatura.
     *
     * Se a temperatura (Celsius) estiver acima de 30 graus, sugere gênero musical festa
     * Se a temperatura está entre 15 e 30 graus, , sugere gênero musical pop
     * Entre 10 e 14 graus, sugere gênero musical rock
     * Abaixo de 10 graus, sugere gênero musical clássico.
     *
     * @param float|string $temperature Valor numérico com a temperatura em graus celsius
     *
     * @return string O gênero musical
     *
     * @throws InvalidInputException
     */
    public static function chooseGenreFromTemperature($temperature)
    {
        if (!is_numeric($temperature)) {
            throw new InvalidInputException('chooseGenreFromTemperature: informe um valor numérico para a temperatura');
        }
        if ($temperature > 30) {
            return self::PARTY;
        } elseif ($temperature >= 15 && $temperature <= 30) {
            return self::POP;
        } elseif ($temperature >= 10 && $temperature < 15) {
            return self::ROCK;
        } else {
            return self::CLASSICAL;
        }
    }
}
