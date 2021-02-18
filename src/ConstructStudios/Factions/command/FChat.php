<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ConstructStudios\Factions\Loader;

class FChat extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "chat";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Toggle faction chat";
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [];
    }

    /**
     * @param bool $player
     * @return array
     */
    public function getUsage(bool $player = true): array {
        return [];
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
            $member->setFactionChatOn($member->isFactionChatOn() == false);

            $player->sendMessage(Loader::ALERT_GREEN . "Your faction chat is now " . ($member->isFactionChatOn() ? TextFormat::GREEN . "ON" : TextFormat::RED . "OFF"));
        }while(false);
    }
}