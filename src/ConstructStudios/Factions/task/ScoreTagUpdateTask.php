<?php
namespace ConstructStudios\Factions\task;

use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use ConstructStudios\Factions\Loader;

class ScoreTagUpdateTask extends Task {

    /** @var Loader */
    protected $loader;

    /**
     * ScoreTagUpdateTask constructor.
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
            if(($member = $this->loader->getMember($player)) !== null){
                $player->setScoreTag(str_replace([
                    "{display_name}",
                    "{name}",
                    "{ping}",
                    "{deviceOS}",
                    "{faction_name}",
                    "{faction_rank}",
                    "{health}"
                ], [
                    $player->getDisplayName(),
                    $player->getName(),
                    $player->getPing() < 120 ? TextFormat::GREEN . $player->getPing() : TextFormat::YELLOW . $player->getPing(),
                    $this->loader->getDeviceOS($player),
                    $member->getFaction() !== null ? $member->getFaction()->getName() : "",
                    $member->getFaction() !== null ? ($member->getFaction()->getLeader()->getName() == $player->getName() ? "**" : "*") : "",
                    $player->getHealth()
                ], TextFormat::colorize($this->loader->getConfig()->get("scoretag-format"))));
            }
        }
    }
}