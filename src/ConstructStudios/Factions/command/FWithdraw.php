<?php
namespace ConstructStudios\Factions\command;

use onebone\economyapi\EconomyAPI;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use ConstructStudios\Factions\Loader;

class FWithdraw extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "withdraw";
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [
            "wd",
            "cash"
        ];
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Withdraw money from your faction balance";
    }

    /**
     * @param bool $player
     * @return array
     */
    public function getUsage(bool $player = true): array {
        return [
            "[amount: int]"
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
            if($member->isCanWithdrawMoney() == false){
                $player->sendMessage(Loader::ALERT_RED . "You don't have permission to withdraw");

                break;
            }
            if(isset($args[0]) == false){
                $this->sendUsage(0, $player);

                break;
            }
            if(is_numeric($args[0]) == false or $args[0] < 1){
                $player->sendMessage(Loader::ALERT_RED . "Withdraw amount must be higher than 0");

                break;
            }
            if($member->getFaction()->getBalance() < (float) $args[0]){
                $player->sendMessage(Loader::ALERT_RED . "You faction doesn't have that much balance");

                break;
            }

            $member->getFaction()->removeBalance((float)$args[0]);
            EconomyAPI::getInstance()->addMoney($player, (float)$args[0]);

            $player->sendMessage(Loader::ALERT_GREEN . "You've withdrawn $" . $args[0] . " from faction balance");
        }while(false);
    }
}