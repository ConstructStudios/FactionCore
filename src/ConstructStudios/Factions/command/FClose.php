<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use ConstructStudios\Factions\Loader;

class FClose extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "close";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Close your faction for new recruits";
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
        return [
            "cl"
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
            if($member->isCanOpenFaction() == false){
                $player->sendMessage(Loader::ALERT_RED . "You don't have permission to close for new recruitment");

                break;
            }
            if($member->getFaction()->isOpen() == false){
                $player->sendMessage(Loader::ALERT_RED . "Your faction is already closed for recruits!");

                break;
            }

            $member->getFaction()->setOpen(false);
            $member->getFaction()->broadcastMessage(Loader::ALERT_YELLOW . $member->getFaction()->getName() . ": Faction is now closed for new recruits, Action by " . $player->getName() . ".");
        }while(false);
    }
}