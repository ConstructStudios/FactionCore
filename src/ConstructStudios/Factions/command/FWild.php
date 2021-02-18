<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use ConstructStudios\Factions\Loader;

class FWild extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "wild";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Teleport to wild";
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [];
    }

    /**
     * @param bool $player
     * @return array
     */
    public function getUsage(bool $player = true): array {
        return [];
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
    public function onPlayerRun(Player $player, array $args): void {
        do{
            $worlds = $this->getLoader()->getConfig()->get("faction-claim-enable");

            if($worlds === []){
                $player->sendMessage(Loader::ALERT_RED . "No worlds found, please contact the developer.");

                break;
            }
            $world = $worlds[array_rand($worlds)];
            if(($level = $this->getLoader()->getServer()->getLevelByName($world)) == null){
                $player->sendMessage(Loader::ALERT_RED . "World not loaded, please contact the developer.");

                break;
            }

            $range = $this->getLoader()->getConfig()->get("faction-wild-range") / 2;
            $vec = $level->getSpawnLocation();
            $vec->setComponents(mt_rand(-$range, $range), 0, mt_rand(-$range, $range));

            $level->getChunk($vec->x >> 4, $vec->z >> 4, true);
            $vec->y = max(71, $level->getHighestBlockAt($vec->x, $vec->z));

            $player->teleport($vec);
        }while(false);
    }
}