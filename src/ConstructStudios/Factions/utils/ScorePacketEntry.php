<?php
namespace ConstructStudios\Factions\utils;

use pocketmine\network\mcpe\protocol\types\ScorePacketEntry as PM;

class ScorePacketEntry extends PM {

    /**
     * ScorePacketEntry constructor.
     * @param string $objectiveName
     * @param string $customName
     * @param int $scoreboardId
     */
    public function __construct(string $objectiveName, string $customName, int $scoreboardId) {
        $this->objectiveName = $objectiveName;
        $this->customName = $customName . str_repeat(" ", 5);
        $this->score = $scoreboardId;
        $this->scoreboardId = $scoreboardId;
        $this->type = self::TYPE_FAKE_PLAYER;
    }
}