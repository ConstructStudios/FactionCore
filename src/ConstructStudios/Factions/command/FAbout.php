<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class FAbout extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "about";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "About our factions system";
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
     * @param CommandSender $sender
     * @param array $args
     */
    public function onRun(CommandSender $sender, array $args): void {
        $sender->sendMessage(TextFormat::colorize(implode("\n&r", yaml_parse_file($this->getLoader()->getDataFolder() . "about.yml"))));
    }
}