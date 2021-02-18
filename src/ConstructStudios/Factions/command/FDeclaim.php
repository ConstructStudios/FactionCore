<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\item\Item;
use pocketmine\level\format\Chunk;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ConstructStudios\Factions\Loader;
use ConstructStudios\Factions\tile\MobSpawner;

class FDeclaim extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "declaim";
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [
            "unclaim"
        ];
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Declaim a land";
    }

    /**
     * @param bool $player
     * @return array
     */
    public function getUsage(bool $player = true): array {
        return [
            "[radius: int|null]"
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
            if($member->isCanDeclaimLands() == false){
                $player->sendMessage(Loader::ALERT_RED . "You don't have permission to declaim");

                break;
            }
            if($args[0] > 20 or $args[0] < 1){
                $player->sendMessage(Loader::ALERT_RED . "You can only declaim 1-20 chunks at once");

                break;
            }
            if(isset($args[0]) == false){
                $this->sendUsage(0, $player);

                break;
            }
            $radius = abs((int) array_shift($args));
            $declaimed = [];
            $factor = $radius % 2 == 0 ? ($radius / 2) - 1 : (($radius - 1) / 2);
            for($x = $player->chunk->getX() - $factor; $x < $player->chunk->getX() + ($factor + 1); $x++){
                for($z = $player->chunk->getZ() - $factor; $z < $player->chunk->getZ() + ($factor + 1); $z++){
                    if($this->getLoader()->getFactionByClaim($x, $z, $player->getLevel()->getName()) !== $member->getFaction()){
                        $player->sendMessage(Loader::ALERT_YELLOW . "Unable to declaim " . $x . ":" . $z . ", not claimed by your faction.");
                        continue;
                    }

                    $declaimed[] = $x . ":" . $z;
                    $member->getFaction()->removeClaim($x, $z, $player->getLevel()->getName());
                    $member->getFaction()->removeValue($this->getChunkValue($player->getLevel()->getChunk($x, $z, true)));
                }
            }

            $member->getFaction()->broadcastMessage(Loader::ALERT_YELLOW . $member->getFaction()->getName() . ": The chunks " . TextFormat::YELLOW . implode(" ", $declaimed) . TextFormat::GRAY . " has been declaimed by " . TextFormat::AQUA . $player->getName());
        }while(false);
    }

    /**
     * @param Chunk $chunk
     * @return float
     */
    protected function getChunkValue(Chunk $chunk): float {
        $value = 0;
        foreach($chunk->getTiles() as $tile){
            if($tile instanceof MobSpawner){
                $id = $tile->getEntityId();
                $spawnerIncome = $this->getLoader()->getConfig()->get("monster-spawner-values", []);
                $price = ($spawnerIncome[$id] ?? $spawnerIncome["default"]) * $tile->getSpawnersStack();

                $value += $price;
            }
        }
        for($x = 0; $x < 15; $x++){
            for($z = 0; $z < 15; $z++){
                for($y = 0; $y < $chunk->getMaxY(); $y++){
                    $id = $chunk->getBlockId($x, $y, $z);
                    $dmg = $chunk->getBlockData($x, $y, $z);

                    $income = $this->getLoader()->getConfig()->get("claim-income-values");
                    $default = $income["default"];
                    unset($income["default"]);

                    foreach($income as $blockS => $price){
                        $vBlock = Item::fromString($blockS);
                        if($vBlock->getId() == $id){
                            if($vBlock->getDamage() == $dmg or $vBlock->getDamage() == -1){
                                $value += $price;
                            }else{
                                $value += $default;
                            }
                        }
                    }
                }
            }
        }

        return $value;
    }
}