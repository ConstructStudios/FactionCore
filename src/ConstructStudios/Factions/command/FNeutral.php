<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use ConstructStudios\Factions\Loader;

class FNeutral extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "neutral";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Remove enemy flag from a faction";
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return[
            "ne"
        ];
    }

    /**
     * @param bool $player
     * @return array
     */
    public function getUsage(bool $player = true): array {
        return [
            "[faction: string]"
        ];
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
            if($member->isCanEnemyFaction() == false){
                $player->sendMessage(Loader::ALERT_RED . "You don't have permission to flag a faction as neutral");

                break;
            }
            if(isset($args[0]) == false){
                $this->sendUsage(0, $player);

                break;
            }
            $name = array_shift($args);

            if(($target = $this->getLoader()->getFaction($name)) == null){
                $player->sendMessage(Loader::ALERT_RED . "The faction: " . $name . " doesn't exist");

                break;
            }
            if($target === $member->getFaction()){
                $player->sendMessage(Loader::ALERT_RED . "You cannot flag your faction as neutral!");

                break;
            }
            if($member->getFaction()->isEnemy($target) == false){
                $player->sendMessage(Loader::ALERT_RED . "That faction is not on enemy list");

                break;
            }

            $member->getFaction()->removeEnemy($target);
            $member->getFaction()->removeAlly($target);
            $target->removeAlly($member->getFaction());

            $member->getFaction()->broadcastMessage(Loader::ALERT_GREEN . "Faction: " . $name . " is now marked as neutral by " . $player->getName());

            $target->broadcastMessage(Loader::ALERT_GREEN . "Faction: " . $member->getFaction()->getName() . " has marked us as neutral");
        }while(false);
    }
}