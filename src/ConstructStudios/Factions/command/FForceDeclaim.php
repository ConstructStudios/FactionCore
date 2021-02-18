<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use ConstructStudios\Factions\Loader;

class FForceDeclaim extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "forcedeclaim";
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [
            "funclaim",
            "fdeclaim",
            "fdc"
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
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Forcefully declaim a land";
    }

    /**
     * @param bool $player
     * @return array
     */
    public function getUsage(bool $player = true): array {
        return [
            "[radius: int | null]"
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
            if(isset($args[0]) == false){
                $this->sendUsage(0, $player);

                break;
            }
            $radius = abs((int) array_shift($args));

            $factor = $radius % 2 == 0 ? ($radius / 2) - 1 : (($radius - 1) / 2);
            for($x = $player->chunk->getX() - $factor; $x < $player->chunk->getX() + ($factor + 1); $x++){
                for($z = $player->chunk->getZ() - $factor; $z < $player->chunk->getZ() + ($factor + 1); $z++){
                    if(($fac = $this->getLoader()->getFactionByClaim($x, $z, $player->getLevel()->getName())) !== null){
                        $fac->removeClaim($x, $z, $player->getLevel()->getName());
                    }
                }
            }

            $player->sendMessage(Loader::ALERT_YELLOW . ($player->chunk->getX() - $factor) . ":" . ($player->chunk->getZ() - $factor) . " to " . ($player->chunk->getX() + ($factor + 1)) . ":" . ($player->chunk->getZ() + ($factor + 1)) . " has been declaimed");
        }while(false);
    }
}