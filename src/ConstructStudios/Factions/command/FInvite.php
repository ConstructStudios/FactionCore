<?php
namespace ConstructStudios\Factions\command;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use ConstructStudios\Factions\Loader;

class FInvite extends BaseCommand {

    /** @var string[][] */
    public $invites = [];

    /**
     * @return string
     */
    public function getCommand(): string {
        return "invite";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Invite a player to your faction or accept/reject invite";
    }

    /**
     * @param bool $player
     * @return array
     */
    public function getUsage(bool $player = true): array {
        return [
            "[string: (player|accept|reject)]",
            "[string: faction(if accept|reject)]"
        ];
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [
            "inv"
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
            switch($input = array_shift($args)){
                case "accept":
                    if(($member = $this->getLoader()->getMember($player))->getFaction() !== null){
                        $player->sendMessage(Loader::ALERT_RED . "You already have a faction");

                        break;
                    }
                    if(isset($args[0]) == false){
                        $this->sendUsage(1, $player);

                        break;
                    }
                    if(isset($this->invites[$player->getName()][$args[0]])){
                        unset($this->invites[$player->getName()][$args[0]]);
                        $fac = $this->getLoader()->getFaction($args[0]);
                        if(count($fac->getMembers()) >= $this->getLoader()->getConfig()->get("max-members")){
                            $player->sendMessage(Loader::ALERT_RED . "That faction is full");

                            break 2;
                        }
                        $fac->addMember($member);
                        $member->setFaction($args[0]);

                        $fac->broadcastMessage(Loader::ALERT_YELLOW . $fac->getName() . ": " . $player->getName() . " has joined the faction");
                    }else{
                        $player->sendMessage(Loader::ALERT_RED . "You were not invited to faction: " . $args[0]);
                    }
                break;
                case "reject":
                    if(($member = $this->getLoader()->getMember($player))->getFaction() !== null){
                        $player->sendMessage(Loader::ALERT_RED . "You already have a faction");

                        break;
                    }
                    if(isset($args[0]) == false){
                        $this->sendUsage(1, $player);

                        break;
                    }
                    if(isset($this->invites[$player->getName()][$args[0]])){
                        unset($this->invites[$player->getName()][$args[0]]);
                        $fac = $this->getLoader()->getFaction($args[0]);

                        $fac->broadcastMessage(Loader::ALERT_YELLOW . $fac->getName() . ": " . $player->getName() . " has rejected faction invitation");
                        $player->sendMessage(Loader::ALERT_YELLOW . "You have rejected the invitation");
                    }else{
                        $player->sendMessage(Loader::ALERT_RED . "You were not invited to faction: " . $args[0]);
                    }
                break;
                default:
                    if(($member = $this->getLoader()->getMember($player))->getFaction() == null){
                        $player->sendMessage(Loader::ALERT_RED . "You don't have a faction");

                        break;
                    }
                    if($member->isCanInviteMember() == false){
                        $player->sendMessage(Loader::ALERT_RED . "You don't have permission to invite!");

                        break;
                    }
                    if(($victim = $this->getLoader()->getServer()->getPlayer($input)) == null){
                        $player->sendMessage(Loader::ALERT_RED . "That player was not found on the server");

                        break;
                    }
                    if($this->getLoader()->getMember($victim)->getFaction() !== null){
                        $player->sendMessage(Loader::ALERT_RED . $victim->getName() . " is already in a faction");

                        break;
                    }
                    if(isset($this->invites[$victim->getLowerCaseName()][$member->getFaction()->getName()])){
                        $player->sendMessage(Loader::ALERT_YELLOW . $victim->getName() . " has been invited recently, please wait for their response");

                        break;
                    }

                    $this->invites[$victim->getName()][$member->getFaction()->getName()] = true;
                    $victim->sendMessage(Loader::ALERT_YELLOW . $player->getName() . " has invited you to join " . $member->getFaction()->getName() . ". Invitation lasts 120 seconds.");
                    $player->sendMessage(Loader::ALERT_GREEN . "Successfully invited " . $victim->getName());

                    $this->getLoader()->getScheduler()->scheduleDelayedTask(new class($this, $victim, $member->getFaction()->getName()) extends Task {
                        /** @var FInvite */
                        protected $cmd;

                        /** @var Player */
                        protected $victim;

                        /** @var string */
                        protected $fac;

                        /**
                         *  constructor.
                         * @param FInvite $cmd
                         * @param Player $victim
                         * @param string $fac
                         */
                        public function __construct(FInvite $cmd, Player $victim, string $fac) {
                            $this->cmd = $cmd;
                            $this->victim = $victim;
                            $this->fac = $fac;
                        }

                        /**
                         * @param int $currentTick
                         */
                        public function onRun(int $currentTick) {
                            unset($this->cmd->invites[$this->victim->getName()][$this->fac]);
                        }
                    }, 20 * 120);
            }
        }while(false);
    }
}