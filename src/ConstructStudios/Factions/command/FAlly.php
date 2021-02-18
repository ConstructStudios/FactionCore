<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use ConstructStudios\Factions\Loader;

class FAlly extends BaseCommand {

    /** @var array */
    public $requests = [];

    /**
     * @return string
     */
    public function getCommand(): string {
        return "ally";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Ally a faction";
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [
            "al"
        ];
    }

    /**
     * @param bool $player
     * @return array
     */
    public function getUsage(bool $player = true): array {
        return [
            "[faction: string]"
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
            if($member->isCanAllyFaction() == false){
                $player->sendMessage(Loader::ALERT_RED . "You don't have permission to ally a faction");

                break;
            }
            if(isset($args[0]) == false){
                $this->sendUsage(0, $player);

                break;
            }
            $name = array_shift($args);

            if(($target = $this->getLoader()->getFaction($name)) == null){
                $player->sendMessage(Loader::ALERT_RED . "The faction: " . $name . " doesn't exist");

                break;
            }
            if($target === $member->getFaction()){
                $player->sendMessage(Loader::ALERT_RED . "You cannot ally your own faction!");

                break;
            }
            if($member->getFaction()->isEnemy($target)){
                $player->sendMessage(Loader::ALERT_RED . "That faction is an enemy!");

                break;
            }
            if($member->getFaction()->isAlly($target)){
                $player->sendMessage(Loader::ALERT_RED . "That faction is already an ally!");

                break;
            }
            if(isset($this->requests[$target->getName()][$member->getFaction()->getName()])){
                $player->sendMessage(Loader::ALERT_RED . "You've already sent an ally request to that faction, please wait!");

                break;
            }
            if(isset($this->requests[$member->getFaction()->getName()][$target->getName()])){
                $target->addAlly($member->getFaction());
                $member->getFaction()->addAlly($target);

                $member->getFaction()->broadcastMessage(Loader::ALERT_GREEN . $member->getFaction()->getName() . ": We are now allied with " . $target->getName());
                $target->broadcastMessage(Loader::ALERT_GREEN . $target->getName() . ": We are now allied with " . $member->getFaction()->getName());
                unset($this->requests[$member->getFaction()->getName()][$target->getName()]);
            }else{
                $this->requests[$target->getName()][$member->getFaction()->getName()] = true;

                $player->sendMessage(Loader::ALERT_GREEN . "Ally request was sent to " . $target->getName() . ", request is valid for 120s.");
                $target->broadcastMessage(Loader::ALERT_YELLOW . $target->getName() . ": A member of Faction: " . $member->getFaction()->getName() . " has sent an ally request, run /faction ally " . $member->getFaction()->getName() . " to confirm. This request only lasts for 120s.");

                $this->getLoader()->getScheduler()->scheduleDelayedTask(new class($member->getFaction()->getName(), $target->getName(), $this) extends Task {
                    /** @var string */
                    protected $p1;

                    /** @var string */
                    protected $p2;

                    protected $obj;

                    /**
                     *  constructor.
                     * @param string $p1
                     * @param string $p2
                     * @param FAlly $obj
                     */
                    public function __construct(string $p1, string $p2, FAlly $obj) {
                        $this->p1 = $p1;
                        $this->p2 = $p2;
                        $this->obj = $obj;
                    }

                    /**
                     * @param int $currentTick
                     */
                    public function onRun(int $currentTick) {
                        unset($this->obj->requests[$this->p1][$this->p2]);
                    }
                }, 20 * 120);
            }
        }while(false);
    }
}