<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use ConstructStudios\Factions\Loader;

class FClaimList extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "claimlist";
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [
            "cllist"
        ];
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Get a list of claims of your faction";
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
            if(($member = $this->getLoader()->getMember($player))->getFaction() == null){
                $player->sendMessage(Loader::ALERT_RED . "You don't have a faction");

                break;
            }
            if($member->isCanClaimLands() == false){
                $player->sendMessage(Loader::ALERT_RED . "You don't have permission to see claim list");

                break;
            }

            $player->sendMessage(Loader::ALERT_YELLOW . "Claims: " . implode(", ", array_map(function(array $in): string {
                    return $in[0] . ":" . $in[1] . " in " . $in[2];
                }, $member->getFaction()->getClaims())));
        }while(false);
    }
}