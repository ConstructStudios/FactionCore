<?php
namespace ConstructStudios\Factions\command;

use onebone\economyapi\EconomyAPI;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ConstructStudios\Factions\Loader;

class FDeposit extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "deposit";
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [
            "depo",
            "donate"
        ];
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Deposit money to your faction";
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
            if(isset($args[0]) == false){
                $this->sendUsage(0, $player);

                break;
            }
            if(is_numeric($args[0]) == false or $args[0] < 1){
                $player->sendMessage(Loader::ALERT_RED . "Deposit amount must be higher than 0");

                break;
            }
            if(EconomyAPI::getInstance()->myMoney($player) < (float) $args[0]){
                $player->sendMessage(Loader::ALERT_RED . "You don't have that much money");

                break;
            }
            $balance = (float)$args[0];

            EconomyAPI::getInstance()->reduceMoney($player, $balance);

            $member->getFaction()->addBalance($balance);

            $member->getFaction()->broadcastMessage(Loader::ALERT_GREEN . $member->getFaction()->getName() . ": " . TextFormat::YELLOW . $player->getName() . TextFormat::GRAY . " deposited $" . $args[0] . " to the faction");
        }while(false);
    }
}