<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\CommandSender;
use ConstructStudios\Factions\faction\Member;
use ConstructStudios\Factions\Loader;

class FPurge extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "purge";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Delete a faction entirely";
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [
            "pur"
        ];
    }

    /**
     * @return bool
     */
    public function isAdminCommand(): bool {
        return true;
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
     * @param CommandSender $sender
     * @param array $args
     */
    public function onRun(CommandSender $sender, array $args): void {
        do{
            if($sender->isOp() == false){
                $sender->sendMessage(Loader::ALERT_YELLOW . "Ehhhhhhh! What is this?");

                break;
            }
            if(count($args) < 1){
                $this->sendUsage(count($args), $sender);

                break;
            }
            $name = array_shift($args);
            if(($target = $this->getLoader()->getFaction($name)) == null){
                $sender->sendMessage(Loader::ALERT_RED . "The faction: " . $name . " doesn't exist");

                break;
            }

            $member = $target->getLeader();
            foreach($member->getFaction()->getMembers() as $m){
                $m->resetPermissions();
                $m->setRank(Member::MEMBER);
                $m->setFaction("");
            }
            $member->resetPermissions();
            $member->setRank(Member::MEMBER);

            $this->getLoader()->removeFaction($target);
            $member->setFaction("");

            $sender->sendMessage(Loader::ALERT_GREEN . "Successfully purged " . $name);
        }while(false);
    }
}