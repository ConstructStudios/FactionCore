<?php
namespace ConstructStudios\Factions\level\generator;

use pocketmine\level\biome\Biome;
use pocketmine\level\generator\biome\BiomeSelector as Selector;

class BiomeSelector extends Selector {

    /**
     * @param float $temperature
     * @param float $rainfall
     * @return int
     */
    protected function lookup(float $temperature, float $rainfall) : int{
        if($rainfall < 0.25){
            if($temperature < 0.7){
                return Biome::OCEAN;
            }elseif($temperature < 0.85){
                return Biome::RIVER;
            }else{
                return Biome::SWAMP;
            }
        }elseif($rainfall < 0.60){
            if($temperature < 0.25){
                return mt_rand(0, 1) == 1 ? Biome::ICE_PLAINS : Biome::MOUNTAINS;
            }elseif($temperature < 0.75){
                return Biome::PLAINS;
            }else{
                return Biome::DESERT;
            }
        }elseif($rainfall < 0.80){
            if($temperature < 0.25){
                return Biome::TAIGA;
            }elseif($temperature < 0.75){
                return Biome::FOREST;
            }else{
                return Biome::BIRCH_FOREST;
            }
        }else{
            return Biome::RIVER;
        }
    }
}