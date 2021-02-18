<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ConstructStudios\Factions\Loader;

class FHud extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "hud";
    }

    /**
     * @param bool $player
     * @return array
     */
    public function getUsage(bool $player = true): array {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [];
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Toggle faction hud (scoreboard)";
    }

    /**
     * @param ConsoleCommandSender $console
     * @param array $args
     */
    protected function onConsoleRun(ConsoleCommandSender $console, array $args): void {
        $this->noConsole($console);
    }

    /**
     * @param Player $player
     * @param array $args
     */
    protected function onPlayerRun(Player $player, array $args): void {
        $member = $this->getLoader()->getMember($player);
        $val = !$member->isHudOn();
        $member->setIsHudOn($val);

        if($val){
            $player->sendMessage(Loader::ALERT_GREEN . "Your scoreboard is now turned " . TextFormat::DARK_GREEN . "ON");
        }else{
            $player->sendMessage(Loader::ALERT_GREEN . "Your scoreboard is now turned " . TextFormat::RED . "OFF");

            $pk = new RemoveObjectivePacket();
            $pk->objectiveName = "FC";
            $player->sendDataPacket($pk);
        }
    }
}