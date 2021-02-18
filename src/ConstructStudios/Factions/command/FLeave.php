<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use ConstructStudios\Factions\faction\Member;
use ConstructStudios\Factions\Loader;

class FLeave extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "leave";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Leave faction";
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
            "bye",
            "l"
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
            if($member->getFaction()->getLeader() === $member){
                $player->sendMessage(Loader::ALERT_RED . "You can't leave the faction as you're the leader");

                break;
            }

            $member->getFaction()->broadcastMessage(Loader::ALERT_YELLOW . $member->getFaction()->getName() . ": " . $player->getName() . " has left the faction");
            $member->getFaction()->removeMember($member);

            $member->resetPermissions();
            $member->setRank(Member::RECRUITMENT);
            $member->setFaction("");
        }while(false);
    }
}