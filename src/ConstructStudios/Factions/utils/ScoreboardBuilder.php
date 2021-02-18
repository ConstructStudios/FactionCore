<?php
namespace ConstructStudios\Factions\utils;

use onebone\economyapi\EconomyAPI;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ConstructStudios\Factions\faction\Member;

class ScoreboardBuilder {

    /**
     * @param float $number
     * @return string
     */
    public static function shortNumber(float $number): string {
        $abbrevs = [
            12 => 'T',
            9 => 'B',
            6 => 'M',
            3 => 'K',
            0 => ''
        ];

        foreach($abbrevs as $exponent => $abbrev){
            if(abs($number) >= pow(10, $exponent)){
                $display = $number / pow(10, $exponent);
                $decimals = ($exponent >= 3 && round($display) < 100) ? 1 : 0;
                $number = number_format($display, $decimals) . $abbrev;

                break;
            }
        }

        return $number;
    }

    /**
     * @param Member $member
     * @param Player $player
     * @return DataPacket[]
     */
    public static function build(Member $member, Player $player): array {
        $ping = $player->getPing();
        if($ping < 120){
            $ping = TextFormat::GREEN . $ping;
        }elseif($ping < 200){
            $ping = TextFormat::GOLD . $ping;
        }elseif($ping < 300){
            $ping = TextFormat::RED . $ping;
        }else{
            $ping = TextFormat::DARK_RED . $ping;
        }

        $experience = $player->getCurrentTotalXp();

        $pks = [];

        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = "FC";
        $pks[] = $pk;

        $pk = new SetDisplayObjectivePacket();
        $pk->objectiveName = "FC";
        $pk->criteriaName = "dummy";
        $pk->displaySlot = "sidebar";
        $pk->displayName = TextFormat::BOLD . TextFormat::RED . "«" . " " .TextFormat::BOLD . TextFormat::DARK_RED . "MTwinz" . " " .TextFormat::BOLD . TextFormat::RED . "»";
        $pk->sortOrder = 0;
        $pks[] = $pk;

        $pk = new SetScorePacket();
        $i = 0;
        $pk->type = SetScorePacket::TYPE_CHANGE;
        $pk->entries[] = new ScorePacketEntry("FC", TextFormat::RESET . "     ", 0);
        $pk->entries[] = new ScorePacketEntry("FC", TextFormat::DARK_RED . " • " . TextFormat::RED ."Balance: " . TextFormat::WHITE . "$" . self::shortNumber(EconomyAPI::getInstance()->myMoney($player)), ++$i);
        $pk->entries[] = new ScorePacketEntry("FC", TextFormat::DARK_RED . " • " . TextFormat::RED ."Activity: " . TextFormat::WHITE . self::shortNumber($member->getActivityPoints()), ++$i);
        $pk->entries[] = new ScorePacketEntry("FC", TextFormat::DARK_RED . " • " . TextFormat::RED ."Ping: " . TextFormat::WHITE . $ping . "ms", ++$i);
        $pk->entries[] = new ScorePacketEntry("FC", TextFormat::DARK_RED . " • " . TextFormat::RED ."XP: " . TextFormat::WHITE . $experience, ++$i);
        $pk->entries[] = new ScorePacketEntry("FC", TextFormat::DARK_RED . " • " . TextFormat::RED ."TPS: " . TextFormat::WHITE . $player->getServer()->getTicksPerSecond(), ++$i);
        $pk->entries[] = new ScorePacketEntry("FC", "   ", ++$i);

        $map = MapBuilder::build($member, $player);
        foreach($map as $line){
            $pk->entries[] = new ScorePacketEntry("FC", str_repeat(TextFormat::WHITE, $i) . "  " . $line, ++$i);

        }
        $pks[] = $pk;

        return $pks;
    }
}