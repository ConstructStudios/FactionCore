<?php
namespace ConstructStudios\Factions\task;

use pocketmine\scheduler\Task;

use ConstructStudios\Factions\Loader;

class STRGainTask extends Task {

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
            foreach($faction->getMembers() as $member){
				// $faction->broadcastMessage(Loader::ALERT_GREEN . $faction->getName() . ": +" . $am . " SAMPLE MESSAGE");
                if($member->getPlayer()->isOnline()){
                    $faction->addSTR($am = $loader->getConfig()->get("str-gain-auto"));
                    $faction->broadcastMessage(Loader::ALERT_GREEN . $faction->getName() . ": +" . $am . " STR (Activity Gain)");

                    break;
                }
            }
        }
    }
}