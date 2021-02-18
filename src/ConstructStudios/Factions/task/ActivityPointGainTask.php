<?php
namespace ConstructStudios\Factions\task;

use pocketmine\scheduler\Task;
use ConstructStudios\Factions\Loader;

class ActivityPointGainTask extends Task {

    /** @var Loader */
    protected $loader;

    /**
     * ActivityPointGainTask constructor.
     * @param Loader $loader
     */
    public function __construct(Loader $loader) {
        $this->loader = $loader;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) {
        foreach($this->loader->getServer()->getOnlinePlayers() as $player){
            if($this->loader->getMember($player) !== null){
                $this->loader->getMember($player)->addActivityPoints($this->loader->getConfig()->get("activity-point-gain"));
            }
        }
    }
}