<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\CommandSender;
use ConstructStudios\Factions\Loader;

class FAddAP extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "addap";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Add activity points to a player";
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [
            "aap"
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
            "[player: string]",
            "[amount: float]"
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
            if(count($args) < 2){
                $this->sendUsage(count($args), $sender);

                break;
            }
            $name = array_shift($args);
            $amount = array_shift($args);

            if(($target = $this->getLoader()->getMemberByName($name)) == null){
                $sender->sendMessage(Loader::ALERT_RED . "User " . $name . " was never seen on the server");

                break;
            }
            if(is_numeric($amount) == false or $amount < 1){
                $sender->sendMessage(Loader::ALERT_RED . "Invalid amount");

                break;
            }

            $target->addActivityPoints(floatval($amount));
            $sender->sendMessage(Loader::ALERT_GREEN . "Added " . $amount . " activity points to " . $target->getName() . "'s account.");
        }while(false);
    }
}