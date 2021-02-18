<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ConstructStudios\Factions\faction\Member;
use ConstructStudios\Factions\Loader;

class FRank extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "rank";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Manage faction members rank";
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [
            "r"
        ];
    }

    /**
     * @param bool $player
     * @return array
     */
    public function getUsage(bool $player = true): array {
        return [
            "[member: string]",
            "[rank: recruit|member|officer|leader]"
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
            if($member->isCanManageRanks() == false){
                $player->sendMessage(Loader::ALERT_RED . "You don't have permission to set a member's rank");

                break;
            }
            if(count($args) < 2){
                $this->sendUsage(count($args), $player);

                break;
            }
            $name = array_shift($args);
            $rank = array_shift($args);

            if(($vMember = $this->getLoader()->getMemberByName($name)) == null){
                $player->sendMessage(Loader::ALERT_RED . $name . " was never seen on the server");

                break;
            }
            if($member->getFaction()->isMember($vMember) == false){
                $player->sendMessage(Loader::ALERT_RED . $vMember->getName() . " is not a member of your faction");

                break;
            }
            if($member === $vMember){
                $player->sendMessage(Loader::ALERT_RED . "You cannot rank yourself!");

                break;
            }
            if(Member::getRankPriority($member->getRank()) <= Member::getRankPriority($vMember->getRank())){
                $player->sendMessage(Loader::ALERT_RED . $vMember->getName() . " is high or equal ranked than you");

                break;
            }

            switch($rank ?? ""){
                case "recruit":
                case "r":
                    $situation = Member::getRankPriority($vMember->getRank()) > Member::getRankPriority(Member::RECRUITMENT) ? "demoted" : "demoted kinda";
                    $vMember->setRank(Member::RECRUITMENT);
                    $vMember->resetPermissions();

                    $member->getFaction()->broadcastMessage(Loader::ALERT_YELLOW . $member->getFaction()->getName() . ": " . $name . " has been " . $situation . " to " . TextFormat::YELLOW . "Recruit" . TextFormat::GRAY . " by " . $player->getName());

                break;
                case "member":
                case "m":
                    $situation = Member::getRankPriority($vMember->getRank()) > Member::getRankPriority(Member::RECRUITMENT) ? "demoted" : "promoted";
                    $vMember->setRank(Member::MEMBER);
                    $vMember->setMemberPermissions();

                    $member->getFaction()->broadcastMessage(Loader::ALERT_YELLOW . $member->getFaction()->getName() . ": " . $name . " has been " . $situation . " to " . TextFormat::YELLOW . "Member" . TextFormat::GRAY . " by " . $player->getName());

                break;
                case "officer":
                case "o":
                    $situation = Member::getRankPriority($vMember->getRank()) > Member::getRankPriority(Member::RECRUITMENT) ? "demoted" : "promoted";
                    $vMember->setRank(Member::OFFICER);
                    $vMember->setOfficerPermissions();

                    $member->getFaction()->broadcastMessage(Loader::ALERT_YELLOW . $member->getFaction()->getName() . ": " . $name . " has been " . $situation . " to " . TextFormat::YELLOW . "Officer" . TextFormat::GRAY . " by " . $player->getName());

                break;
                case "leader":
                case "l":
                    $situation = Member::getRankPriority($vMember->getRank()) > Member::getRankPriority(Member::RECRUITMENT) ? "demoted" : "promoted";
                    $vMember->setRank(Member::LEADER);
                    $vMember->grantAllPermissions();

                    $member->getFaction()->broadcastMessage(Loader::ALERT_YELLOW . $member->getFaction()->getName() . ": " . $name . " has been " . $situation . " to " . TextFormat::YELLOW . "Leader" . TextFormat::GRAY . " by " . $player->getName());

                    break;
                default:
                    $player->sendMessage(Loader::ALERT_RED . "Unknown rank, valid ranks: recruit(r), member(m), officer(o), leader(l)");
            }
        }while(false);
    }
}
