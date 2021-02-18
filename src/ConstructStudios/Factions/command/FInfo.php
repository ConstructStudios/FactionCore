<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use ConstructStudios\Factions\faction\Member;
use ConstructStudios\Factions\Loader;
use ConstructStudios\Factions\utils\ScoreboardBuilder;

class FInfo extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "info";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Get information about a faction";
    }

    /**
     * @param bool $player
     * @return array
     */
    public function getUsage(bool $player = true): array {
        return [
            "[name: string]"
        ];
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [
            "inf",
            "data"
        ];
    }

    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function onRun(CommandSender $sender, array $args): void {
        do{
            if(isset($args[0]) == false){
                $this->sendUsage(0, $sender);

                break;
            }
            if(($fac = $this->getLoader()->getFaction($name = array_shift($args))) == null){
                $sender->sendMessage(Loader::ALERT_RED . "No faction was found with name: " . $name);

                break;
            }

            $sender->sendMessage(Loader::ALERT_YELLOW . $fac->getName() . "'s Information:");
            $sender->sendMessage(TextFormat::BOLD.TextFormat::DARK_RED . "| " . TextFormat::RESET.TextFormat::RED . "Description: " . TextFormat::GRAY . $fac->getDescription());
            $sender->sendMessage(TextFormat::BOLD.TextFormat::DARK_RED . "| " . TextFormat::RESET.TextFormat::RED . "Leader: " . ($fac->getLeader()->getPlayer()->isOnline() ? TextFormat::GREEN : TextFormat::RED) . $fac->getLeader()->getName());
            $sender->sendMessage(TextFormat::BOLD.TextFormat::DARK_RED . "| " . TextFormat::RESET.TextFormat::RED . "Members: \n" . implode("\n ", array_map(function(Member $member): string {
                    return ($member->getPlayer()->isOnline() ? TextFormat::GREEN : TextFormat::RED) . $member->getName();
                }, $fac->getMembers())));
            $sender->sendMessage(TextFormat::BOLD.TextFormat::DARK_RED . "| " . TextFormat::RESET.TextFormat::RED . "Claims: " . TextFormat::GRAY . count($fac->getClaims()));
            $sender->sendMessage(TextFormat::BOLD.TextFormat::DARK_RED . "| " . TextFormat::RESET.TextFormat::RED . "Balance: " . TextFormat::GRAY . "$" . $fac->getBalance());
            $sender->sendMessage(TextFormat::BOLD.TextFormat::DARK_RED . "| " . TextFormat::RESET.TextFormat::RED . "Value: " . TextFormat::GRAY . "$" . ScoreboardBuilder::shortNumber($fac->getValue()));
            $sender->sendMessage(TextFormat::BOLD.TextFormat::DARK_RED . "| " . TextFormat::RESET.TextFormat::RED . "STR: " . TextFormat::GRAY . ScoreboardBuilder::shortNumber($fac->getSTR()));
            $sender->sendMessage(TextFormat::BOLD.TextFormat::DARK_RED . "| " . TextFormat::RESET.TextFormat::RED . "DTR: " . TextFormat::GRAY . $fac->getDTR());
            $sender->sendMessage(TextFormat::BOLD.TextFormat::DARK_RED . "| " . TextFormat::RESET.TextFormat::RED . "Rank: " . TextFormat::GRAY . (array_search($fac, $this->getLoader()->getTopFactions()) + 1));

        }while(false);
    }
}