<?php
namespace ConstructStudios\Factions\task;

use pocketmine\scheduler\Task;

use ConstructStudios\Factions\Loader;

class DTRGainTask extends Task {

    /** @var Loader */
    protected $loader;

    /**
     * DTRGainTask constructor.
     * @param Loader $loader
     */
    public function __construct(Loader $loader) {
        $this->loader = $loader;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) {
        $loader = $this->loader;

        foreach($loader->getFactions() as $faction){
            if($faction->getDTR() < $loader->getConfig()->get("dtr-max")){
                $faction->addDTR(1);
            }
			$faction->addBalance($faction->getValue());
        }
    }
}