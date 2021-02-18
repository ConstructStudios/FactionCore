<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use ConstructStudios\Factions\faction\Member;
use ConstructStudios\Factions\Loader;

class FSetHome extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "sethome";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Set your faction home";
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
            "sh"
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
            if(Member::getRankPriority($member->getRank()) < Member::getRankPriority(Member::OFFICER)){
                $player->sendMessage(Loader::ALERT_RED . "You don't have permission to set home!");

                break;
            }
            if($this->getLoader()->getFactionByClaim($player->x >> 4, $player->z >> 4, $player->getLevel()->getName()) !== $member->getFaction()){
                $player->sendMessage(Loader::ALERT_RED . "You must be in a claim to set home!");

                break;
            }

            $member->getFaction()->setHome($player->asPosition());
            $member->getFaction()->broadcastMessage(Loader::ALERT_YELLOW . $member->getFaction()->getName() . ": Faction home is updated by " . $player->getName());
        }while(false);
    }
}