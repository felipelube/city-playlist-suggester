<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Services\MusicGenreChooser;

class MusicGenreChooserTest extends TestCase
{
    public function testWithIntegers()
    {
        $temperature = 42;
        $this->assertEquals(MusicGenreChooser::chooseGenreFromTemperature($temperature), MusicGenreChooser::PARTY);

        $temperature = 30;
        $this->assertEquals(MusicGenreChooser::chooseGenreFromTemperature($temperature), MusicGenreChooser::POP);

        $temperature = 14;
        $this->assertEquals(MusicGenreChooser::chooseGenreFromTemperature($temperature), MusicGenreChooser::ROCK);

        $temperature = 9;
        $this->assertEquals(MusicGenreChooser::chooseGenreFromTemperature($temperature), MusicGenreChooser::CLASSICAL);

        $temperature = 0;
        $this->assertEquals(MusicGenreChooser::chooseGenreFromTemperature($temperature), MusicGenreChooser::CLASSICAL);

        $temperature = -9;
        $this->assertEquals(MusicGenreChooser::chooseGenreFromTemperature($temperature), MusicGenreChooser::CLASSICAL);

    }

    public function testWithFloats()
    {
        $temperature = 30.1;
        $this->assertEquals(MusicGenreChooser::chooseGenreFromTemperature($temperature), MusicGenreChooser::PARTY);

        $temperature = 29.9;
        $this->assertEquals(MusicGenreChooser::chooseGenreFromTemperature($temperature), MusicGenreChooser::POP);

        $temperature = "14.5";
        $this->assertEquals(MusicGenreChooser::chooseGenreFromTemperature($temperature), MusicGenreChooser::ROCK);

        $temperature = 9.9;
        $this->assertEquals(MusicGenreChooser::chooseGenreFromTemperature($temperature), MusicGenreChooser::CLASSICAL);

        $temperature = -9.9;
        $this->assertEquals(MusicGenreChooser::chooseGenreFromTemperature($temperature), MusicGenreChooser::CLASSICAL);
    }

    public function testWithStrings()
    {
        $temperature = "30.1";
        $this->assertEquals(MusicGenreChooser::chooseGenreFromTemperature($temperature), MusicGenreChooser::PARTY);

        $temperature = "29.9";
        $this->assertEquals(MusicGenreChooser::chooseGenreFromTemperature($temperature), MusicGenreChooser::POP);

        $temperature = "14.5";
        $this->assertEquals(MusicGenreChooser::chooseGenreFromTemperature($temperature), MusicGenreChooser::ROCK);

        $temperature = "9.9";
        $this->assertEquals(MusicGenreChooser::chooseGenreFromTemperature($temperature), MusicGenreChooser::CLASSICAL);

        $temperature = "-9";
        $this->assertEquals(MusicGenreChooser::chooseGenreFromTemperature($temperature), MusicGenreChooser::CLASSICAL);
    }

    public function testWithNonNumericValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        MusicGenreChooser::chooseGenreFromTemperature("abacate");
    }

    public function testWithEmptyValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        MusicGenreChooser::chooseGenreFromTemperature("");
    }
}
