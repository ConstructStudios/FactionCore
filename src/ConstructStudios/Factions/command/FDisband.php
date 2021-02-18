<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use ConstructStudios\Factions\faction\Member;
use ConstructStudios\Factions\Loader;

class FDisband extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "disband";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Disband your faction";
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
            "delete",
            "shutdown"
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
            if($member->getFaction()->getLeader() !== $member){
                $player->sendMessage(Loader::ALERT_RED . "You can't disband the faction!");

                break;
            }

            $member->getFaction()->broadcastMessage(Loader::ALERT_RED . $member->getFaction()->getName() . ": Faction has been disbanded");

            foreach($member->getFaction()->getMembers() as $m){
                $m->resetPermissions();
                $m->setRank(Member::MEMBER);
                $m->setFaction("");
            }
            $member->resetPermissions();
            $member->setRank(Member::MEMBER);

            $this->getLoader()->removeFaction($member->getFaction());
            $member->setFaction("");
        }while(false);
    }
}