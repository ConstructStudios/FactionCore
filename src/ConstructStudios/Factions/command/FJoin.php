<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use ConstructStudios\Factions\Loader;

class FJoin extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "join";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Join an open faction";
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
     * @return array
     */
    public function getAliases(): array {
        return [
            "j"
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
            if(($member = $this->getLoader()->getMember($player))->getFaction() !== null){
                $player->sendMessage(Loader::ALERT_RED . "You already have a faction");

                break;
            }
            if(isset($args[0]) == false){
                $this->sendUsage(0, $player);

                break;
            }
            if(($fac = $this->getLoader()->getFaction($args[0])) == null){
                $player->sendMessage(Loader::ALERT_RED . "That faction doesn't exist!");

                break;
            }
            if(count($fac->getMembers()) >= $this->getLoader()->getConfig()->get("max-members")){
                $player->sendMessage(Loader::ALERT_RED . "That faction is full!");

                break;
            }
            if($fac->isOpen() == false){
                $player->sendMessage(Loader::ALERT_RED . "That faction isn't recruiting new members!");

                break;
            }

            $fac->addMember($member);
            $member->setFaction($fac->getName());

            $fac->broadcastMessage(Loader::ALERT_YELLOW . $fac->getName() . ": " . $player->getName() . " has joined the faction");
        }while(false);
    }
}