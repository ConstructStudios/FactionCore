<?php
namespace ConstructStudios\Factions\command;

use pocketmine\item\Item;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\level\format\Chunk;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ConstructStudios\Factions\Loader;
use ConstructStudios\Factions\tile\MobSpawner;

class FClaim extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "claim";
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [
            "cla"
        ];
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Declare a land as your faction territory";
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
     * @throws \ReflectionException
     */
    protected function onPlayerRun(Player $player, array $args): void {
        if(!isset($args[0])){
            $player->sendMessage(TextFormat::GRAY . "/f claim (2-20)");
            return;
        }
        do{
            if(($member = $this->getLoader()->getMember($player))->getFaction() == null){
                $player->sendMessage(Loader::ALERT_RED . "You don't have a faction");

                break;
            }
            if($member->isCanClaimLands() == false){
                $player->sendMessage(Loader::ALERT_RED . "You don't have permission to claim");

                break;
            }
            if($args[0] > 20 or $args[0] < 1){
                $player->sendMessage(Loader::ALERT_RED . "You can only claim 1-20 chunks at once");

                break;
            }
            if(in_array($player->getLevel()->getName(), $this->getLoader()->getConfig()->get("faction-claim-enable")) == false){
                $player->sendMessage(Loader::ALERT_RED . "Claiming is not enabled here!");

                break;
            }
            if(isset($args[0]) == false){
                $this->sendUsage(0, $player);

                break;
            }

            $lands = 0;
            $radius = abs((int)array_shift($args));
            $factor = $radius % 2 == 0 ? ($radius / 2) - 1 : (($radius - 1) / 2);

            for($x = $player->chunk->getX() - $factor; $x < $player->chunk->getX() + ($factor + 1); $x++){
                for($z = $player->chunk->getZ() - $factor; $z < $player->chunk->getZ() + ($factor + 1); $z++){
                    $lands++;
                }
            }
            if($member->getFaction()->getSTR() < ($req = $lands * $this->getLoader()->getConfig()->get("claim-land-cost"))){
                $player->sendMessage(Loader::ALERT_RED . "Your faction bank doesn't have enough STR, required STR: " . $req);

                break;
            }

            $notClaimed = [];
            $claimed = [];
            for($x = $player->chunk->getX() - $factor; $x < $player->chunk->getX() + ($factor + 1); $x++){
                for($z = $player->chunk->getZ() - $factor; $z < $player->chunk->getZ() + ($factor + 1); $z++){
                    if($this->getLoader()->getFactionByClaim($x, $z, $player->getLevel()->getName()) !== null){
                        $notClaimed[] = $x . ":" . $z;
                        $req -= $this->getLoader()->getConfig()->get("claim-land-cost");

                        continue;
                    }

                    $block = $player->getLevel()->getBlock(new Vector3($x * 16, 64, $z * 16));
                    $ev = new BlockBreakEvent($player, $block, Item::get(Item::AIR), true, []);
                    $ev->call();
                    if($ev->isCancelled()){
                        $notClaimed[] = $x . ":" . $z;
                        $req -= $this->getLoader()->getConfig()->get("claim-land-cost");

                        continue;
                    }

                    $claimed[] = $x . ":" . $z;
                    $member->getFaction()->addClaim($x, $z, $player->getLevel()->getName());
                    $member->getFaction()->addValue($this->getChunkValue($player->getLevel()->getChunk($x, $z, true)));
                }
            }
            $member->getFaction()->broadcastMessage(Loader::ALERT_YELLOW . $member->getFaction()->getName() . ": The chunks " . TextFormat::YELLOW . implode(" ", $claimed) . TextFormat::GRAY . " has been claimed by " . TextFormat::AQUA . $player->getName());
            $player->sendMessage(Loader::ALERT_YELLOW . $member->getFaction()->getName() . ": The chunks " . TextFormat::YELLOW . implode(" ", $notClaimed) . TextFormat::GRAY . " is already claimed, STR not needed is returned");

            $member->getFaction()->removeSTR($req);
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