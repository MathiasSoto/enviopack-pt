<?php

abstract class WatchAbstract{

    private $consumption_unit = 1;
    private $digits = 6;
    protected $segments_by_numbers = [
        0 => 6,
        1 => 2,
        2 => 5,
        3 => 5,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 3,
        8 => 7,
        9 => 5,
    ];

    public abstract function getGastoEnergetico(int $seconds):int;

    protected function getSegmentsByNumber($number):int{

        if($number >= 0 && $number <= 9){
            return $this->segments_by_numbers[$number];
        }
    }

    /**
     * @return int
     */
    public function getConsumptionUnit():int
    {
        return $this->consumption_unit;
    }

    /**
     * @param int $consumption_unit
     * @return Watch
     */
    public function setConsumptionUnit($consumption_unit)
    {
        $this->consumption_unit = $consumption_unit;
        return $this;
    }

    /**
     * @return int
     */
    public function getDigits(): int
    {
        return $this->digits;
    }

    /**
     * @param int $digits
     * @return Watch
     */
    public function setDigits(int $digits): Watch
    {
        $this->digits = $digits;
        return $this;
    }

    protected function getTime($seconds):string{

        return gmdate("H:i:s", $seconds);
    }

    protected function getConsumptionByTime(string $time):int{

        $time_data = explode(":", $time);

        $hours_segments = $this->getSegmentsByNumber((int) $time_data[0][0]) + $this->getSegmentsByNumber((int) $time_data[0][1]);
        $minutes_segments = $this->getSegmentsByNumber((int) $time_data[1][0]) + $this->getSegmentsByNumber((int) $time_data[1][1]);
        $seconds_segments = $this->getSegmentsByNumber((int) $time_data[2][0]) + $this->getSegmentsByNumber((int) $time_data[2][1]);

        return (
            ($hours_segments * $this->getConsumptionUnit()) +
            ($minutes_segments * $this->getConsumptionUnit()) +
            ($seconds_segments * $this->getConsumptionUnit())
        );
    }

}

class StandardWatch extends WatchAbstract {

    public function getGastoEnergetico(int $seconds): int
    {
        $total_consumption = 0;

        for ($i = 0; $i <= $seconds; $i++){

            $time = $this->getTime($i);
            $total_consumption += $this->getConsumptionByTime($time);
        }
        return $total_consumption;
    }

}

class PremiumWatch extends WatchAbstract {

    private $initial_consumption = 36;

    private $segments_by_numbers_difference = [
        0 => 0,
        1 => 0,
        2 => 4,
        3 => 1,
        4 => 1,
        5 => 2,
        6 => 1,
        7 => 1,
        8 => 4,
        9 => 0,
    ];

    public function getGastoEnergetico(int $seconds): int
    {
        $total_consumption = $this->initial_consumption * $this->getConsumptionUnit();

        for ($i = 0; $i <= $seconds; $i++){

            $time = $this->getTime($i);
            $total_consumption += $this->getConsumptionByTime($time);
        }
        return $total_consumption;
    }

    protected function getSegmentsByNumber($number):int{

        if($number >= 0 && $number <= 9){
            return $this->segments_by_numbers_difference[$number];
        }
    }
}


/**
 * Test de uso para los dos tipos de relojes
 */

function printConsumption(WatchAbstract $watch, int $minutes){

    print_r((string)$watch->getGastoEnergetico($minutes)."\n");
}

function printConsumptionWattDay(WatchAbstract $watch, WatchAbstract $watch2){

    $seconds_day = 60*60*24;
    $energy_saving = $watch->getGastoEnergetico($seconds_day) - $watch2->getGastoEnergetico($seconds_day);
    if($energy_saving < 0){
        $energy_saving *= -1;
    }
    //Calculando la equivalencia de watt [W] <—> microwatt [μW]
    $watts = round($energy_saving/1000000,2);
    print_r("Ahorro de energía en watts (w) durante un dia completo respecto de usar un reloj premium vs un reloj estandar :".$watts."\n");
}

//Se espera 172
printConsumption(new StandardWatch(), 4);

//Se espera 42
printConsumption(new PremiumWatch(), 4);

//Calculo de diferencia por dia
printConsumptionWattDay(new StandardWatch(), new PremiumWatch());