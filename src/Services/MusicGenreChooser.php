<?php

namespace App\Services;

class MusicGenreChooser {
  /**
   * Gêneros musicais
   */
  const PARTY = 'party';
  const POP = 'pop';
  const ROCK = 'rock';
  const CLASSICAL = 'classical';

  public static function chooseGenreFromTemperature($temperature) {
    /**
     *  * Se a temperatura (Celsius) estiver acima de 30 graus, sugerir músicas para festa
        * Se a temperatura está entre 15 e 30 graus, sugerir músicas do gênero Pop.
        * Entre 10 e 14 graus, sugerir músicas do gênero Rock
        * Abaixo de 10 graus, segerir músicas clássicas.
     */
    if (!is_numeric($temperature)) {
      throw new \InvalidArgumentException(
        "chooseGenreFromTemperature somente aceita valores numéricos de temperatura"
      );
    }
    if ($temperature > 30)   {
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