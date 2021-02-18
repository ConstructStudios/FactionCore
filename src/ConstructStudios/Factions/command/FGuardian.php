<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use ConstructStudios\Factions\entity\FactionGuardian;
use ConstructStudios\Factions\Loader;

class FGuardian extends BaseCommand {

    /**
     * @return string
     */
    public function getCommand(): string {
        return "guardian";
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [
            "g"
        ];
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Spawn a guardian to protect your claims";
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
            if($member->isCanSpawnGuardian() == false){
                $player->sendMessage(Loader::ALERT_RED . "You don't have permission to spawn a guardian");

                break;
            }
            if($member->getFaction()->getGuardians() >= ($max = $this->getLoader()->getConfig()->get("faction-max-guardians"))){
                $player->sendMessage(Loader::ALERT_RED . "Maximum guardian spawn limit(" . $max . ") has been reached");

                break;
            }
            if($member->getFaction()->getBalance() < ($cost = $this->getLoader()->getConfig()->get("faction-guardian-spawn-cost"))){
                $player->sendMessage(Loader::ALERT_RED . "Your faction bank doesn't have enough money to spawn a guardian, required money: $" . $cost . ".");

                break;
            }
            if(($cf = $this->getLoader()->getFactionByClaim($player->x >> 4, $player->z >> 4, $player->getLevel()->getName())) == null or ($cf !== null and $cf !== $member->getFaction())){
                $player->sendMessage(Loader::ALERT_RED . "You must be in your own faction claim to spawn a guardian");

                break;
            }

            $member->getFaction()->removeBalance($cost);
            $nbt = FactionGuardian::createBaseNBT($player);
            $nbt->setString("Faction", $member->getFaction()->getName());
            (new FactionGuardian($player->getLevel(), $nbt))->spawnToAll();

            $member->getFaction()->setGuardians($member->getFaction()->getGuardians() + 1);

            $player->sendMessage(Loader::ALERT_GREEN . "Successfully spawned the guardian.");
        }while(false);
    }
}