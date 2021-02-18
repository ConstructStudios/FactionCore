<?php
namespace ConstructStudios\Factions\task;

use pocketmine\scheduler\Task;
use ConstructStudios\Factions\Loader;
use ConstructStudios\Factions\utils\ScoreboardBuilder;

class ScoreboardSendTask extends Task {

    /** @var Loader */
    protected $loader;

    /**
     * ScoreboardSendTask constructor.
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
            if(($m = $this->loader->getMember($player)) !== null and $m->isHudOn()){
                foreach(ScoreboardBuilder::build($m, $player) as $packet){
                    $player->sendDataPacket($packet);
                }
            }
        }
    }
}