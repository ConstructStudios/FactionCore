<?php
namespace ConstructStudios\Factions\command;

use onebone\economyapi\EconomyAPI;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use ConstructStudios\Factions\faction\Faction;
use ConstructStudios\Factions\faction\Member;
use ConstructStudios\Factions\Loader;

class FCreate extends BaseCommand {

    public const ALLOWED_CHARS = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";

    public const BANNED_NAMES = [
        "b3duZXI=",
        "YWRtaW4=",
        "c2V4eQ==",
        "ZnCjaw==",
        "cHVzc3k=",
        "Yml0Y2g=",
        "ZGljaw==",
        "Y3VudA==",
        "bmlnZ2E=",
        "ZGlsZG8=",
        "bnVkZQ=="
    ];

    /**
     * @return string
     */
    public function getCommand(): string {
        return "create";
    }

    /**
     * @param bool $player
     * @return string
     */
    public function getDescription(bool $player = true): string {
        return "Create a faction";
    }

    /**
     * @param bool $player
     * @return array
     */
    public function getUsage(bool $player = true): array {
        return [
            "[name: string(char < 12)]",
            "[description: string(char < 25)]"
        ];
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [
            "new"
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
            if($this->getLoader()->getMember($player)->getFaction() !== null){
                $player->sendMessage(Loader::ALERT_RED . "You already have a faction");

                break;
            }
            if(isset($args[0]) == false){
                $this->sendUsage(0, $player);

                break;
            }
            $name = array_shift($args);
            $tests = str_split(self::ALLOWED_CHARS);

            foreach(str_split($name) as $char){
                if(in_array($char, $tests) == false){
                    $player->sendMessage(Loader::ALERT_RED . "Invalid character detected, allowed characters: " . self::ALLOWED_CHARS);

                    break 2;
                }
            }
            foreach(self::BANNED_NAMES as $BANNED_NAME){
                if(strpos(strtolower($name), base64_decode($BANNED_NAME)) !== false){
                    $player->sendMessage(Loader::ALERT_RED . "Hey hey! No using those words!");

                    break 2;
                }
            }
            if(strlen($name) > 12){
                $player->sendMessage(Loader::ALERT_RED . "Character length should not exceed 12 characters");

                break;
            }
            if(strlen($name) < 4){
                $player->sendMessage(Loader::ALERT_RED . "Minimum character length is 4");

                break;
            }
            if($this->getLoader()->getFaction($name) !== null){
                $player->sendMessage(Loader::ALERT_RED . "That name has already been taken");

                break;
            }
            if(EconomyAPI::getInstance()->myMoney($player) < ($p = $this->getLoader()->getConfig()->get("faction-create-price"))){
                $player->sendMessage(Loader::ALERT_RED . "You need $" . $p . " to create a new faction");

                break;
            }

            EconomyAPI::getInstance()->reduceMoney($player, $p);
            $this->getLoader()->addFaction(Faction::new($player->getName(), $name, implode(" ", empty($args) ? [""] : $args)));
            $this->getLoader()->getMember($player)->setFaction($name);
            $this->getLoader()->getMember($player)->grantAllPermissions();
            $this->getLoader()->getMember($player)->setRank(Member::LEADER);

            $player->sendMessage(Loader::ALERT_GREEN . "You've created faction: " . $name);
        }while(false);
    }
}