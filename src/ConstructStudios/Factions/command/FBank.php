<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ConstructStudios\Factions\faction\Faction;
use ConstructStudios\Factions\Loader;
use ConstructStudios\Factions\tile\MobSpawner;

class FBank extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "bank";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Check faction bank value";
    }

    /**
     * @param bool $player
     * @return array
     */
    public function getUsage(bool $player = true): array {
        return [
            "[string: (faction|empty]"
        ];
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [
            "value"
        ];
    }

    /**
     * @param CommandSender $player
     * @param array $args
     */
    public function onRun(CommandSender $player, array $args): void {
        do{
            if(isset($args[0]) == false){
                if($player instanceof Player){
                    if(($fac = $this->getLoader()->getMember($player)->getFaction()) == null){
                        $player->sendMessage(Loader::ALERT_RED . "You don't have a faction");

                        break;
                    }
                    $income = $this->calculateHourlyIncome($fac);

                    $player->sendMessage(Loader::ALERT_YELLOW . $fac->getName() . "'s bank value: $" . $fac->getBalance());
                    $player->sendMessage(Loader::ALERT_YELLOW . "Hourly Income:");
                    $player->sendMessage("  " . TextFormat::GOLD . "Spawners: " . TextFormat::GRAY . "$" . $income["spawners"]);
                    $player->sendMessage("  " . TextFormat::GOLD . "Claims: " . TextFormat::GRAY . "$" . $income["claims"]);
                }else{
                    $this->sendUsage(0, $player);
                }
            }else{
                $fac = $this->getLoader()->getFaction($args[0]);
                if($fac == null){
                    $player->sendMessage(Loader::ALERT_RED . "That faction doesn't exist");

                    break;
                }
                $income = $this->calculateHourlyIncome($fac);

                $player->sendMessage(Loader::ALERT_YELLOW . $fac->getName() . "'s bank value: $" . $fac->getBalance());
                $player->sendMessage(Loader::ALERT_YELLOW . "Hourly Income:");
                $player->sendMessage("  - " . TextFormat::GOLD . "Spawners: " . TextFormat::GRAY . "$" . $income["spawners"]);
                $player->sendMessage("  - " . TextFormat::GOLD . "Claims: " . TextFormat::GRAY . "$" . $income["claims"]);
            }
        }while(false);
    }

    /**
     * @param Faction $faction
     * @return array
     */
    protected function calculateHourlyIncome(Faction $faction): array {
        $data = ["claims" => 0, "spawners" => 0];

        foreach($faction->getClaims() as $claimData){
            $level = $this->getLoader()->getServer()->getLevelByName($claimData[2]);
            if($level == null){
                $this->getLoader()->getServer()->loadLevel($claimData[2]);
                $level = $this->getLoader()->getServer()->getLevelByName($claimData[2]);
            }
            if($level !== null){
                $chunk = $level->getChunk($claimData[0], $claimData[1], true);

                $incomeS = $this->getLoader()->getConfig()->get("monster-spawner-values", []);
                foreach($chunk->getTiles() as $tile){
                    if($tile instanceof MobSpawner){
                        $id = $tile->getEntityId();
                        $data["spawners"] += ($incomeS[$id] ?? $incomeS["default"]) * $tile->getSpawnersStack();
                    }
                }
            }
        }

        $data["claims"] = min(0, $faction->getValue() - $data["spawners"]);

        return $data;
    }
}