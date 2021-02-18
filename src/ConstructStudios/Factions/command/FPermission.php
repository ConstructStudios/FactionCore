<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ConstructStudios\Factions\faction\Member;
use ConstructStudios\Factions\Loader;

class FPermission extends BaseCommand {

    public const PERMISSIONS = [
        "touch" => "setCanTouch",
        "build" => "setCanBuild",
        "invite" => "setCanInviteMember",
        "kick" => "setCanKickMember",
        "open" => "setCanOpenFaction",
        "close" => "setCanCloseFaction",
        "permissions" => "setCanManagePermissions",
        "rank" => "setCanManageRanks",
        "guardian" => "setCanSpawnGuardian",
        "claim" => "setCanClaimLands",
        "declaim" => "setCanDeclaimLands",
        "withdraw" => "setCanWithdrawMoney",
        "ally" => "setCanAllyFaction",
        "enemy" => "setCAnEnemyFaction",

    ];

    /**
     * @return array
     */
    protected function getPermissions(): array {
        return array_keys(self::PERMISSIONS);
    }

    /**
     * @return string
     */
    public function getCommand(): string {
        return "permission";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Manage faction member permissions";
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return[
            "p",
            "perm"
        ];
    }

    /**
     * @param bool $player
     * @return array
     */
    public function getUsage(bool $player = true): array {
        return [
            "[member: string]",
            "[perm: string]",
            "[value: true|false]"
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
            if($member->isCanManagePermissions() == false){
                $player->sendMessage(Loader::ALERT_RED . "You don't have permission to manage permissions!");

                break;
            }
            if(count($args) < 3){
                $this->sendUsage(count($args), $player);

                break;
            }
            $name = array_shift($args);
            $perm = array_shift($args);
            $val = (bool) array_shift($args);

            if(($vMember = $this->getLoader()->getMemberByName($name)) == null){
                $player->sendMessage(Loader::ALERT_RED . $name . " was never seen on the server");

                break;
            }
            if($member->getFaction()->isMember($vMember) == false){
                $player->sendMessage(Loader::ALERT_RED . $vMember->getName() . " is not a member of your faction");

                break;
            }
            if($member === $vMember){
                $player->sendMessage(Loader::ALERT_RED . "You already have all the permissions!");

                break;
            }
            if(Member::getRankPriority($member->getRank()) <= Member::getRankPriority($vMember->getRank())){
                $player->sendMessage(Loader::ALERT_RED . $vMember->getName() . " is high or equal ranked than you");

                break;
            }
            if(in_array($perm, $this->getPermissions()) == false){
                $player->sendMessage(Loader::ALERT_RED . "Unknown permission node, available nodes:\n" . implode("\n- ", $this->getPermissions()));

                break;
            }

            $com = self::PERMISSIONS[$perm];
            $vMember->$com($val);

            $player->sendMessage(Loader::ALERT_GREEN . "Permission node " . TextFormat::AQUA . $perm . TextFormat::GRAY . " was set to " . ($val ? "true" : "false") . " for " . $name);
        }while(false);
    }
}