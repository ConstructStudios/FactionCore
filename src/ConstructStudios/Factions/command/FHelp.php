<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class FHelp extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "help";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Get a list of commands";
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
        return [
            "h",
            "?"
        ];
    }

    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function onRun(CommandSender $sender, array $args): void {
        $page = (int) ($args[0] ?? 1);
        $page = max(1, $page);
        $cmds = [];
        foreach($this->getLoader()->getCommands() as $commnada){
            if($commnada->isAdminCommand() and $sender->isOp() == false){
                continue;
            }

            $cmds[] = $commnada;
        }
        $commands = array_chunk($cmds, 5);

        if(isset($commands[$page - 1]) == false){
            $page = 1;
        }
        $sender->sendMessage(TextFormat::GRAY . "Showing help " . TextFormat::DARK_GRAY . "[" . TextFormat::RED . $page . TextFormat::GRAY . " out of " . TextFormat::DARK_RED . count($commands) . TextFormat::DARK_GRAY . "]");
        foreach($commands[$page - 1] as $command){
            /** @var BaseCommand $command */
            $sender->sendMessage(TextFormat::DARK_RED . "§7§c§l» §r" . TextFormat::RED . $command->getCommand() . TextFormat::GRAY . " - " . $command->getDescription($sender instanceof Player) . TextFormat::GOLD . " USAGE: /faction " . $command->getCommand() . " " . TextFormat::GRAY . implode(" ", $command->getUsage($sender instanceof Player)));
        }
    }
}