<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use ConstructStudios\Factions\faction\Member;
use ConstructStudios\Factions\Loader;

class FKick extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "kick";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Kick a player from the faction";
    }

    /**
     * @param bool $player
     * @return array
     */
    public function getUsage(bool $player = true): array {
        return [
            "[string: (member)]"
        ];
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [
            "ki"
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
            if($member->isCanKickMember() == false){
                $player->sendMessage(Loader::ALERT_RED . "You don't have permission to kick a member");

                break;
            }
            if(isset($args[0]) == false){
                $this->sendUsage(0, $player);

                break;
            }
            if(($vMember = $this->getLoader()->getMemberByName($args[0])) == null){
                $player->sendMessage(Loader::ALERT_RED . $args[0] . " was never seen on the server");

                break;
            }
            if($member->getFaction()->isMember($vMember) == false){
                $player->sendMessage(Loader::ALERT_RED . $vMember->getName() . " is not a member of your faction");

                break;
            }
            if($member === $vMember){
                $player->sendMessage(Loader::ALERT_RED . "Wait a minute, that's you!");

                break;
            }
            if(Member::getRankPriority($member->getRank()) <= Member::getRankPriority($vMember->getRank())){
                $player->sendMessage(Loader::ALERT_RED . $vMember->getName() . " is high or equal ranked than you");

                break;
            }

            $member->getFaction()->broadcastMessage(Loader::ALERT_YELLOW . $member->getFaction()->getName() . ": " . $player->getName() . " kicked out " . $vMember->getName());
            $member->getFaction()->removeMember($vMember);

            $vMember->resetPermissions();
            $vMember->setRank(Member::RECRUITMENT);
            $vMember->setFaction("");
        }while(false);
    }
}