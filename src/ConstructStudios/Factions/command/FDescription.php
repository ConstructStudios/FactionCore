<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ConstructStudios\Factions\faction\Member;
use ConstructStudios\Factions\Loader;

class FDescription extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "description";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Set the description of your faction";
    }

    /**
     * @param bool $player
     * @return array
     */
    public function getUsage(bool $player = true): array {
        return [
            "[description: string(char < 60)]"
        ];
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [
            "desc",
            "motd"
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
            if($member->getRank()!== Member::LEADER){
                $player->sendMessage(Loader::ALERT_RED . "You don't have permission to edit description");

                break;
            }
            if(strlen($desc = implode(" ", $args)) > 60){
                $player->sendMessage(Loader::ALERT_RED . "Description length must not exceed 60 characters");

                break;
            }

            $member->getFaction()->setDescription(TextFormat::colorize($desc));
            $player->sendMessage(Loader::ALERT_GREEN . "Faction description was updated successfully.");
        }while(false);
    }
}