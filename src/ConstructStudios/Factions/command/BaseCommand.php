<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ConstructStudios\Factions\Loader;

abstract class BaseCommand {

    /**
     * @return string
     */
    abstract public function getCommand(): string;

    /**
     * @param bool $player
     * @return string
     */
    abstract public function getDescription(bool $player = true): string;

    /**
     * @param bool $player
     * @return array
     * [string that indicates an usage part]
     */
    abstract public function getUsage(bool $player = true): array;

    /**
     * @return array
     */
    public function getAliases(): array {
        return [];
    }

    /**
     * @return bool
     */
    public function isAdminCommand(): bool {
        return false;
    }

    /**
     * @param ConsoleCommandSender $console
     * @param array $args
     */
    protected function onConsoleRun(ConsoleCommandSender $console, array $args): void {

    }

    /**
     * @param Player $player
     * @param array $args
     */
    protected function onPlayerRun(Player $player, array $args): void {

    }

    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function onRun(CommandSender $sender, array $args): void {
        if($sender instanceof Player){
            $this->onPlayerRun($sender, $args);
        }elseif($sender instanceof ConsoleCommandSender){
            $this->onConsoleRun($sender, $args);
        }else{
            throw new \InvalidArgumentException();
        }
    }

    /**
     * @return Loader
     */
    protected function getLoader(): Loader {
        return Loader::getInstance();
    }

    /**
     * @param int $argsDone
     * @param CommandSender $sender
     */
    protected function sendUsage(int $argsDone, CommandSender $sender): void {
        $args = $this->getUsage($sender instanceof Player);
        $done = [];
        for($i = 0; $i < $argsDone; $i++){
            $done[] = array_shift($args);
        }

        $sender->sendMessage(Loader::ALERT_RED . TextFormat::GOLD . "USAGE: " . TextFormat::GRAY . "/faction " . $this->getCommand() . " " . TextFormat::GREEN . implode(" ", $done) . " " . TextFormat::RED . implode(" ", $args));
    }

    /**
     * @param ConsoleCommandSender $sender
     */
    protected function noConsole(ConsoleCommandSender $sender): void {
        $sender->sendMessage(TextFormat::RED . "This command cannot be ran via console.");
    }
}