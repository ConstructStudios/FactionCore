<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use ConstructStudios\Factions\faction\Faction;
use ConstructStudios\Factions\Loader;
use ConstructStudios\Factions\utils\ScoreboardBuilder;

class FTop extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "top";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Get a list of top factions";
    }

    /**
     * @param bool $player
     * @return array
     */
    public function getUsage(bool $player = true): array {
        return [
            "[page: int]"
        ];
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [];
    }

    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function onRun(CommandSender $sender, array $args): void {
        do{
            $page = abs(intval(array_shift($args)));
            $page = $page == 0 ? 1 : $page;
            $top = array_chunk($this->getLoader()->getTopFactions(), 10, true);

            if(isset($top[$page - 1]) == false){
                $sender->sendMessage(Loader::ALERT_RED . "That page doesn't exist!");

                break;
            }

            $sender->sendMessage(TextFormat::BOLD . TextFormat::DARK_RED . "TOP FACTIONS LIST " . TextFormat::RESET . TextFormat::GRAY . "[" . TextFormat::RED . $page . TextFormat::GRAY . " out of " . TextFormat::DARK_RED . count($top) . TextFormat::GRAY . "]");
            foreach($top[$page - 1] as $rank => $fac){
                /** @var Faction $fac */
                $sender->sendMessage(TextFormat::RED . "(" . ($rank + 1) . ") " . TextFormat::GRAY . $fac->getName() . TextFormat::RESET . " | " . TextFormat::GREEN . "STR: " . ScoreboardBuilder::shortNumber($fac->getSTR()) .  TextFormat::GRAY . " | " . TextFormat::GREEN . "Value: $" . ScoreboardBuilder::shortNumber($fac->getValue()));
            }
        }while(false);
    }
}