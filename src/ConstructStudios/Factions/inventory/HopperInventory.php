<?php
namespace ConstructStudios\Factions\inventory;

use pocketmine\inventory\ContainerInventory;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use ConstructStudios\Factions\tile\HopperTile;

class HopperInventory extends ContainerInventory {

    /**
     * @param HopperTile $hopper
     */
    public function __construct(HopperTile $hopper){
        parent::__construct($hopper);
    }

    /**
     * @return string
     */
    public function getName() : string{
        return "Item Hopper";
    }

    /**
     * @return int
     */
    public function getDefaultSize() : int{
        return 5;
    }

    /**
     * @return int
     */
    public function getNetworkType() : int{
        return WindowTypes::HOPPER;
    }
}