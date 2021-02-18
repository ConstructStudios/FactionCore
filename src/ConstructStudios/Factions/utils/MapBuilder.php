<?php
namespace ConstructStudios\Factions\utils;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ConstructStudios\Factions\faction\Member;
use ConstructStudios\Factions\Loader;

class MapBuilder {

    /** @var int */
    private static $flicker = true;

    public const ICON = "⬛";

    /**
     * @param Member $member
     * @param Player $player
     * @return string[]
     */
    public static function build(Member $member, Player $player): array {
        $map = [];
        $line = "";
        $mx = $player->x >> 3;
        $mz = $player->z >> 3;

        for($x = $mx - 3; $x < $mx + 4; $x++){
            for($z = $mz - 3; $z < $mz + 4; $z++){
                if($x == $mx and $z == $mz){
                    $line .= (self::$flicker ? TextFormat::GREEN : TextFormat::GRAY) . self::ICON;
                    self::$flicker = !self::$flicker;
                }else{
                    $fac = Loader::getInstance()->getFactionByClaim($x, $z, $player->getLevel()->getName());
                    if($fac !== null){
                        if($fac->isAlly($member->getFaction())){
                            $line .= TextFormat::LIGHT_PURPLE . self::ICON;
                        }elseif($fac === $member->getFaction()){
                            $line .= TextFormat::GREEN . self::ICON;
                        }else{
                            $line .= TextFormat::RED . self::ICON;
                        }
                    }else{
                        $line .= TextFormat::GRAY . self::ICON;
                    }
                }
            }

            $map[] = $line;
            $line = "";
        }

        return $map;
    }
}