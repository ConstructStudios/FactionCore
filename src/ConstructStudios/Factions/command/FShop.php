<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use ConstructStudios\Factions\Loader;

class FShop extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "shop";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Open faction shop interface";
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [
            "sh"
        ];
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
    protected function onPlayerRun(Player $player, array $args): void {
        do{
            if($player->y < 2 or $player->y > 252){
                $player->sendMessage(Loader::ALERT_RED . "You cannot open faction shop here, you're either too high up or too down underground.");

                break;
            }

            $this->getLoader()->openFactionShopTo($player);
        }while(false);
    }
}