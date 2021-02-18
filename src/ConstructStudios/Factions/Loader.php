<?php
namespace ConstructStudios\Factions;

use onebone\economyapi\EconomyAPI;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

use pocketmine\entity\Entity;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ItemSpawnEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\inventory\transaction\action\SlotChangeAction;

use pocketmine\IPlayer;

use pocketmine\item\Item;

use pocketmine\level\generator\GeneratorManager;
use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\tile\Tile;

use pocketmine\utils\TextFormat;

use ConstructStudios\Factions\block\Hopper;


use ConstructStudios\Factions\command\BaseCommand;
use ConstructStudios\Factions\command\FAbout;
use ConstructStudios\Factions\command\FAddAP;
use ConstructStudios\Factions\command\FAlly;
use ConstructStudios\Factions\command\FBank;
use ConstructStudios\Factions\command\FChat;
use ConstructStudios\Factions\command\FClaim;
use ConstructStudios\Factions\command\FClaimList;
use ConstructStudios\Factions\command\FClose;
use ConstructStudios\Factions\command\FCreate;
use ConstructStudios\Factions\command\FDeclaim;
use ConstructStudios\Factions\command\FDeposit;
use ConstructStudios\Factions\command\FDescription;
use ConstructStudios\Factions\command\FDisband;
use ConstructStudios\Factions\command\FEnemy;
use ConstructStudios\Factions\command\FForceDeclaim;
use ConstructStudios\Factions\command\FGuardian;
use ConstructStudios\Factions\command\FHelp;
use ConstructStudios\Factions\command\FHome;
use ConstructStudios\Factions\command\FHud;
use ConstructStudios\Factions\command\FInfo;
use ConstructStudios\Factions\command\FInvite;
use ConstructStudios\Factions\command\FJoin;
use ConstructStudios\Factions\command\FKick;
use ConstructStudios\Factions\command\FLeave;
use ConstructStudios\Factions\command\FNeutral;
use ConstructStudios\Factions\command\FOpen;
use ConstructStudios\Factions\command\FPermission;
use ConstructStudios\Factions\command\FPurge;
use ConstructStudios\Factions\command\FRank;
use ConstructStudios\Factions\command\FRemoveAP;
use ConstructStudios\Factions\command\FSetHome;
use ConstructStudios\Factions\command\FShop;
use ConstructStudios\Factions\command\FTop;
use ConstructStudios\Factions\command\FWild;
use ConstructStudios\Factions\command\FWithdraw;

use ConstructStudios\Factions\entity\Creeper;
use ConstructStudios\Factions\entity\FactionGuardian;
use ConstructStudios\Factions\entity\vanilla\Blaze;
use ConstructStudios\Factions\entity\vanilla\Chicken;
use ConstructStudios\Factions\entity\vanilla\Cow;
use ConstructStudios\Factions\entity\vanilla\Enderman;
use ConstructStudios\Factions\entity\vanilla\IronGolem;
use ConstructStudios\Factions\entity\vanilla\Pig;
use ConstructStudios\Factions\entity\vanilla\Sheep;
use ConstructStudios\Factions\entity\vanilla\Skeleton;
use ConstructStudios\Factions\entity\vanilla\ZombiePigman;

use ConstructStudios\Factions\faction\Faction;
use ConstructStudios\Factions\faction\Member;

use ConstructStudios\Factions\inventory\FactionShopInventory;

use ConstructStudios\Factions\level\generator\FactionGenerator;
use ConstructStudios\Factions\task\ActivityPointGainTask;
use ConstructStudios\Factions\task\DTRGainTask;
use ConstructStudios\Factions\task\MiscTask;
use ConstructStudios\Factions\task\ScoreboardSendTask;
use ConstructStudios\Factions\task\ScoreTagUpdateTask;

use ConstructStudios\Factions\task\STRGainTask;
use ConstructStudios\Factions\tile\HopperTile;
use ConstructStudios\Factions\utils\FactionShopItem;

class Loader extends PluginBase implements Listener {

    public const ALERT_RED = TextFormat::BOLD . TextFormat::RED . "! " . TextFormat::RESET . TextFormat::GRAY;

    public const ALERT_YELLOW = TextFormat::BOLD . TextFormat::YELLOW . "! " . TextFormat::RESET . TextFormat::GRAY;

    public const ALERT_GREEN = TextFormat::BOLD . TextFormat::GREEN . "! " . TextFormat::RESET . TextFormat::GRAY;

    public const CONFIG = "2.0.2";

    /** @var self */
    private static $instance;

    /** @var Member[] */
    protected $members = [];

    /** @var Faction[] */
    protected $factions = [];

    /** @var BaseCommand[] */
    protected $commands = [];

    /** @var string[] */
    protected $deviceOS = [];

    /** @var FactionShopItem[][] */
    protected $factionShopPages = [];

    /**
     * @return BaseCommand[]
     */
    public function getCommands(): array {
        return $this->commands;
    }

    /**
     * @return Loader
     */
    public static function getInstance(): self {
        return self::$instance;
    }

    public function onLoad() {
        self::$instance = $this;
        $this->saveResource("config.yml");
        $this->saveResource("faction_shop.yml");
        $this->saveResource("about.yml");

        if(($v = $this->getConfig()->get("version", 0)) !== self::CONFIG){
            $this->getLogger()->info(TextFormat::YELLOW . "Incompatible config version detected, replacing with compatible...");

            rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config.yml" . $v);
            $this->saveResource("config.yml", true);
            $this->saveResource("about.txt", true);
            $this->getConfig()->reload();
        }

        foreach(yaml_parse_file($this->getDataFolder() . "faction_shop.yml") as $key => $data){
            try{
                $this->factionShopPages[] = new FactionShopItem($data);
            }catch(\Exception $exception){
                $this->getLogger()->logException($exception);
            }
        }
        $this->factionShopPages = array_chunk($this->factionShopPages, 18);
    }

    /**
     * @throws \ReflectionException
     */
    public function onEnable() {
        if(file_exists($this->getDataFolder() . "factions/")){
            foreach(glob($this->getDataFolder() . "factions/*") as $path){
                try{
                    $faction = new Faction();
                    $faction->loadData(yaml_parse_file($path));

                    $this->factions[$faction->getName()] = $faction;
                }catch(\Throwable $exception){
                    $this->getLogger()->logException($exception);
                }
            }
        }
        if(file_exists($this->getDataFolder() . "members/")){
            foreach(glob($this->getDataFolder() . "members/*") as $path){
                try{
                    $member = new Member();
                    $member->loadData(yaml_parse_file($path));

                    $this->members[strtolower($member->getName())] = $member;
                }catch(\Throwable $exception){
                    $this->getLogger()->logException($exception);
                }
            }
        }

        Entity::registerEntity(Creeper::class, true, ["raid_egg"]);
        Entity::registerEntity(FactionGuardian::class, true, ["faction_guardian"]);

        Tile::registerTile(HopperTile::class, [
            "minecraft:hopper",
            "Hopper"
        ]);

        BlockFactory::registerBlock(new Hopper(), true);

        Item::initCreativeItems();

        GeneratorManager::addGenerator(FactionGenerator::class, "faction", true);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->getScheduler()->scheduleRepeatingTask(new MiscTask($this), 200);
        $this->getScheduler()->scheduleRepeatingTask(new ScoreboardSendTask($this), 20);
        $this->getScheduler()->scheduleRepeatingTask(new DTRGainTask($this), 20 * 60 * 60);
		$this->getScheduler()->scheduleRepeatingTask(new STRGainTask($this), 20 * $this->getConfig()->get("auto-str-time"));
        $this->getScheduler()->scheduleRepeatingTask(new ActivityPointGainTask($this), 20);
        $this->getScheduler()->scheduleRepeatingTask(new ScoreTagUpdateTask($this), 20);

        $this->commands = [
            new FCreate(),
            new FLeave(),
            new FInvite(),
            new FKick(),
            new FDisband(),

            new FHelp(),
            new FInfo(),
            new FClaim(),
            new FDeclaim(),
            new FEnemy(),

            new FBank(),
            new FDeposit(),
            new FWithdraw(),
            new FNeutral(),
            new FOpen(),

            new FClose(),
            new FJoin(),
            new FAlly(),
            new FClaimList(),
            new FShop(),

            new FChat(),
            new FAbout(),
            new FTop(),
            new FRank(),
            new FPermission(),

            new FDescription(),
            new FAddAP(),
            new FRemoveAP(),
            new FPurge(),
            new FHud(),

            new FGuardian(),
            new FForceDeclaim(),
            new FWild(),
            new FSetHome(),
            new FHome()

        ];

        foreach(array_diff(scandir($this->getServer()->getDataPath() . "worlds/"), [
            ".",
            ".."
        ]) as $name){
            if(file_exists($this->getServer()->getDataPath() . "worlds/" . $name . "/level.dat")){
                $this->getServer()->loadLevel($name);
            }
        }

        $this->fix();
    }

    protected function fix(): void {

    }

    public function onDisable() {
        if(file_exists($this->getDataFolder() . "members/") == false){
            mkdir($this->getDataFolder() . "members/");
        }
        if(file_exists($this->getDataFolder() . "factions/") == false){
            mkdir($this->getDataFolder() . "factions/");
        }

        foreach($this->factions as $faction){
            yaml_emit_file($this->getDataFolder() . "factions/" . $faction->getName(), $faction->getData());
        }
        foreach($this->members as $member){
            yaml_emit_file($this->getDataFolder() . "members/" . $member->getName(), $member->getData());
        }
    }

    /**
     * @return Member[]
     */
    public function getMembers(): array {
        return $this->members;
    }

    /**
     * @return Faction[]
     */
    public function getFactions(): array {
        return $this->factions;
    }

    /**
     * @param IPlayer $player
     * @return null|Member
     */
    public function getMember(IPlayer $player): ?Member {
        return $this->members[strtolower($player->getName())] ?? null;
    }

    /**
     * @param string $name
     * @return null|Member
     */
    public function getMemberByName(string $name): ?Member {
        $member = $this->members[strtolower($name)] ?? null;

        if($member == null){
            foreach($this->members as $vName => $vMember){
                if(strpos($vName, strtolower($name)) !== false){
                    return $vMember;
                }
            }
        }

        return $member;
    }

    /**
     * @param null|string $name
     * @return null|Faction
     */
    public function getFaction(?string $name): ?Faction {
        return $this->factions[$name] ?? null;
    }

    /**
     * @param Faction $faction
     */
    public function addFaction(Faction $faction): void {
        $this->factions[$faction->getName()] = $faction;
    }

    /**
     * @param Faction $faction
     */
    public function removeFaction(Faction $faction): void {
        unset($this->factions[$faction->getName()]);

        if(is_file($this->getDataFolder() . "factions/" . $faction->getName())){
            unlink($this->getDataFolder() . "factions/" . $faction->getName());
        }
    }

    /**
     * @param Player $player
     * @return string
     */
    public function getDeviceOS(Player $player): string {
        return $this->deviceOS[$player->getLowerCaseName()] ?? "UNKNOWN OS";
    }

    /**
     * @return array
     */
    public function getTopFactions(): array {
        $arr = [];

        foreach($this->factions as $faction){
            $arr[$faction->getName()] = $faction->getSTR();
        }
        arsort($arr);

        $return = [];
        foreach($arr as $name => $value){
            $return[] = $this->factions[$name];
        }

        return $return;
    }

    /**
     * @param int $x
     * @param int $z
     * @param string $level
     * @return null|Faction
     */
    public function getFactionByClaim(int $x, int $z, string $level): ?Faction {
        foreach($this->factions as $faction){
            if($faction->isClaimed($x, $z, $level)){
                return $faction;
            }
        }

        return null;
    }

    /**
     * @param IPlayer $player
     * @return null|Faction
     */
    public function getFactionFor(IPlayer $player): ?Faction {
        return $this->getMember($player)->getFaction();
    }

    /**
     * @param Player $player
     */
    public function openFactionShopTo(Player $player): void {
        $inv = new FactionShopInventory($player->asPosition());
        $page = $this->factionShopPages[0];

        foreach($page as $factionShopItem){
            $inv->addItem($factionShopItem->getDisplay());
        }
        if(isset($this->factionShopPages[1])){
            $inv->setItem(25, Item::get(Item::PAPER)->setCustomName(TextFormat::RESET . TextFormat::RED . ">>"));
        }

        $player->addWindow($inv);
    }

    /**
     * @param InventoryTransactionEvent $event
     * @ignoreCancelled true
     */
    public function onTransaction(InventoryTransactionEvent $event): void {
        $transaction = $event->getTransaction();
        $player = $transaction->getSource();
        $actions = $transaction->getActions();

        foreach($actions as $action){
            if($action instanceof SlotChangeAction){
                if(($inv = $action->getInventory()) instanceof FactionShopInventory){
                    $event->setCancelled();

                    do{
                        if($action->getSlot() == 19){
                            $inv->clearAll();
                            $page = $this->factionShopPages[++$inv->page];

                            foreach($page as $factionShopItem){
                                $inv->addItem($factionShopItem->getDisplay());
                            }
                            break;
                        }
                        if($action->getSlot() == 25){
                            $inv->clearAll();
                            $page = $this->factionShopPages[--$inv->page];

                            foreach($page as $factionShopItem){
                                $inv->addItem($factionShopItem->getDisplay());
                            }
                            break;
                        }
                        /** @var FactionShopItem $shopItem */
                        $shopItem = $this->factionShopPages[$inv->page][$action->getSlot()] ?? null;
                        if($shopItem == null){
                            break 2;
                        }
                        if(($member = $this->getMember($player)) !== null){
                            if($member->getActivityPoints() >= $shopItem->getCost()){
                                $member->removeActivityPoints($shopItem->getCost());

                                if($shopItem->isGrant()){
                                    $player->getInventory()->addItem($shopItem->getItem());
                                }
                                foreach($shopItem->getCommands() as $command){
                                    $this->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace([
                                        "{player}",
                                        "{display_name}",
                                        "{x}",
                                        "{y}",
                                        "{z}",
                                        "{level}",
                                        "{nametag}"
                                    ], [
                                        $player->getName(),
                                        $player->getDisplayName(),
                                        $player->x,
                                        $player->y,
                                        $player->z,
                                        $player->getLevel()->getName(),
                                        $player->getNameTag()
                                    ], $command));
                                }

                                $player->sendMessage(Loader::ALERT_GREEN . "You've bought the item for " . $shopItem->getCost() . "AP");
                            }else{
                                $player->sendMessage(Loader::ALERT_RED . "You don't have enough activity points!");
                                $player->removeWindow($inv);
                            }
                        }
                    }while(false);
                    if(count($inv->getViewers()) > 0){
                        if(isset($this->factionShopPages[$inv->page + 1])){
                            $inv->setItem(25, Item::get(Item::PAPER)->setCustomName(TextFormat::RESET . TextFormat::RED . ">>"));
                        }
                        if(isset($this->factionShopPages[$inv->page - 1])){
                            $inv->setItem(19, Item::get(Item::PAPER)->setCustomName(TextFormat::RESET . TextFormat::RED . "<<"));
                        }
                    }

                    break;
                }
            }
        }
    }

    /**
     * @param DataPacketReceiveEvent $event
     */
    public function onDataReceive(DataPacketReceiveEvent $event): void {
        $pk = $event->getPacket();

        if($pk instanceof LoginPacket){
            switch(intval($pk->clientData["DeviceOS"])){
                case 1:
                    $this->deviceOS[strtolower($pk->username)] = "Android";
                break;
                case 2:
                    $this->deviceOS[strtolower($pk->username)] = "iOS";
                break;
                case 3:
                    $this->deviceOS[strtolower($pk->username)] = "MacOS";
                break;
                case 7:
                    $this->deviceOS[strtolower($pk->username)] = "WIN10";
                break;
                default:
                    $this->deviceOS[strtolower($pk->username)] = "UNKNOWN OS";
            }
        }
    }

    /**
     * @param PlayerMoveEvent $event
     * @ignoreCancelled true
     */
    public function onMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        $level = $player->getLevel();
        $dat = $this->getConfig()->get("world-border");

        if(isset($dat[$level->getName()])){
            $v1 = new Vector3($level->getSpawnLocation()->getX(), 0, $level->getSpawnLocation()->getZ());
            $v2 = new Vector3($player->x, 0, $player->z);

            if($v2->distance($v1) > $dat[$level->getName()]){
                $event->setCancelled();
                $player->sendTip(Loader::ALERT_RED . "You've reached the world border, no more moving forward!");
            }
        }
        if($player->y < 1 and $player->getLevel() === $player->getServer()->getDefaultLevel()){
            $player->teleport($player->getLevel()->getSpawnLocation());
        }

        $fr = $event->getFrom();
        $to = $event->getTo();
        $lvlstr = $player->getLevel()->getName();

        $ff = $this->getFactionByClaim($fr->x >> 4, $fr->z >> 4, $lvlstr);
        $tf = $this->getFactionByClaim($to->x >> 4, $to->z >> 4, $lvlstr);

        if($ff !== null and $tf == null){ // Going to wilderness
            $player->resetTitles();
            $player->addTitle(TextFormat::GRAY . "Wilderness", "It's better to not go alone", 0, 20, 20);
        }elseif($tf !== null and $ff == null){ // Wild to a claim
            $player->resetTitles();
            $player->addTitle(TextFormat::AQUA . $tf->getName(), TextFormat::GRAY . $tf->getDescription(), 0, 20, 20);
        }
    }

    /**
     * @param BlockBreakEvent $event
     * @ignoreCancelled true
     */
    public function onBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        $member = $this->getMember($player);
        $memberFaction = $member->getFaction();
        $baseFaction = $this->getFactionByClaim($block->x >> 4, $block->z >> 4, $player->getLevel()->getName());

        $ignore = false;
        foreach($this->getConfig()->get("claim-ignore-block-break") as $string){
            $vItem = Item::fromString($string);
            if($vItem->getId() == $block->getId()){
                if($vItem->getDamage() == $block->getDamage() or $vItem->getDamage() == -1){
                    $ignore = true;

                    break;
                }
            }
        }

        if($ignore == false){
            if($baseFaction !== null and $baseFaction->getDTR() > 0){
                if($memberFaction !== null and $baseFaction !== null){
                    if($memberFaction === $baseFaction){
                        if($member->isCanBuild() == false){
                            $event->setCancelled();
                            $player->sendMessage(TextFormat::RED . "You don't have permission to edit claims");
                        }
                    }else{
                        $event->setCancelled();
                        $player->sendMessage(TextFormat::RED . "This area is claimed by " . TextFormat::GRAY . $baseFaction->getName());
                    }
                }elseif($memberFaction == null and $baseFaction !== null){
                    $event->setCancelled();
                    $player->sendMessage(TextFormat::RED . "This area is claimed by " . TextFormat::GRAY . $baseFaction->getName());
                }
            }
        }
        if($event->isCancelled() == false){
            $member->addActivityPoints($this->getConfig()->get("activity-point-gain-mine"));

            // this will handle value
            if($baseFaction !== null){
                $income = $this->getConfig()->get("claim-income-values");
                $default = $income["default"];
                unset($income["default"]);

                foreach($income as $blockS => $price){
                    $vBlock = Item::fromString($blockS);
                    if($vBlock->getId() == $block->getId()){
                        if($vBlock->getDamage() == $block->getDamage() or $vBlock->getDamage() == -1){
                            $baseFaction->removeValue($price);

                            continue;
                        }elseif($block->getId() !== 0 and $block->getId() !== Item::MONSTER_SPAWNER){
                            $baseFaction->removeValue($default);
                        }
                    }
                }

                $tile = $block->getLevel()->getTile($block);
            }
        }
    }

    /**
     * @param BlockPlaceEvent $event
     * @ignoreCancelled true
     */
    public function onPlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        $member = $this->getMember($player);
        $memberFaction = $member->getFaction();
        $baseFaction = $this->getFactionByClaim($block->x >> 4, $block->z >> 4, $player->getLevel()->getName());

        if($baseFaction !== null and $baseFaction->getDTR() > 0){
            if($memberFaction !== null and $baseFaction !== null){
                if($memberFaction === $baseFaction){
                    if($member->isCanBuild() == false){
                        $event->setCancelled();
                        $player->sendMessage(TextFormat::RED . "You don't have permission to edit claims");
                    }
                }else{
                    $event->setCancelled();
                    $player->sendMessage(TextFormat::RED . "This area is claimed by " . TextFormat::GRAY . $baseFaction->getName());
                }
            }elseif($memberFaction == null and $baseFaction !== null){
                $event->setCancelled();
                $player->sendMessage(TextFormat::RED . "This area is claimed by " . TextFormat::GRAY . $baseFaction->getName());
            }
        }

        if($event->isCancelled() == false){
            $member->addActivityPoints($this->getConfig()->get("activity-point-gain-build"));

            // this will handle value
            if($baseFaction !== null){
                $income = $this->getConfig()->get("claim-income-values");
                $default = $income["default"];
                unset($income["default"]);

                foreach($income as $blockS => $price){
                    $vBlock = Item::fromString($blockS);
                    if($vBlock->getId() == $block->getId()){
                        if($vBlock->getDamage() == $block->getDamage() or $vBlock->getDamage() == -1){
                            $baseFaction->addValue($price);
                        }else{
                            $baseFaction->addValue($default);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param PlayerInteractEvent $event
     * @ignoreCancelled true
     */
    public function onTouch(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
		$item = $event->getItem();

        $member = $this->getMember($player);
        $memberFaction = $member->getFaction();
        $baseFaction = $this->getFactionByClaim($block->x >> 4, $block->z >> 4, $player->getLevel()->getName());

        $ignore = false;
        foreach($this->getConfig()->get("claim-ignore-block-touch") as $string){
            $vItem = Item::fromString($string);
            if($vItem->getId() == $block->getId()){
                if($vItem->getDamage() == $block->getDamage() or $vItem->getDamage() == -1){
                    $ignore = true;
                    break;
                }
            }
        }

        if($ignore == false){
            if($baseFaction !== null and $baseFaction->getDTR() > 0){
                if($memberFaction !== null and $baseFaction !== null){
                    if($memberFaction === $baseFaction){
                        if($member->isCanBuild() == false){
                            $event->setCancelled();
                            $player->sendMessage(TextFormat::RED . "You don't have permission to edit claims");
                        }
                    }else{
                        $event->setCancelled();
                        $player->sendMessage("§7§c§l» §r§7This area is claimed by §4" .$baseFaction->getName());
                    }
                }elseif($memberFaction == null and $baseFaction !== null){
                    $event->setCancelled();
                    $player->sendMessage("§7§c§l» §r§7This area is claimed by §4" .$baseFaction->getName());
                }
            }
        }
    }

    /**
     * @param PlayerChatEvent $event
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onChat(PlayerChatEvent $event): void {
        $format = $event->getFormat();
        $player = $event->getPlayer();

        $event->setFormat(str_replace([
            "{faction}",
            "{faction_rank}"
        ], [
            $this->getFactionFor($player) !== null ? $this->getFactionFor($player)->getName() : "",
            $this->getFactionFor($player) !== null ? ($this->getMember($player)->getRank()) : ""
        ], $format));

        if(($m = $this->getMember($player))->isFactionChatOn()){
            if($m->getFaction() == null){
                $m->setFactionChatOn(false);

                return;
            }
            $event->setCancelled();

            $m->getFaction()->broadcastMessage($msg = TextFormat::WHITE . $m->getRank() . TextFormat::GOLD . $m->getFaction()->getName() . TextFormat::GRAY . " < " .TextFormat::AQUA. $player->getDisplayName() . TextFormat::GRAY ." > " . TextFormat::GRAY . $event->getMessage());
            $this->getLogger()->info($msg);
        }
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();

        if($player->getGamemode() == Player::SPECTATOR){
            $player->setGamemode(Player::SURVIVAL);
        }
        if(($member = $this->getMember($player)) == null){
            $this->members[$player->getLowerCaseName()] = $member = new Member();
            $member->loadData(["name" => $player->getName()]);

            $this->getServer()->broadcastMessage(str_replace("{player}", $player->getName(), TextFormat::colorize(implode("\n&r", $this->getConfig()->get("first-time-join")))));
        }
    }

    /**
     * @param EntityDamageEvent $event
     * @priority LOWEST
     * @ignoreCancelled true
     * @throws \ReflectionException
     */
    public function onDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();

        if($entity instanceof Player){
            if($event instanceof EntityDamageByEntityEvent) {
                $damager = $event->getDamager();

                if ($damager instanceof Player) {
                    if ($this->getFactionFor($entity) !== null and $this->getFactionFor($entity)->isMember($this->getMember($damager))) {
                        $event->setCancelled();
                    }
                    if ($this->getFactionFor($entity) !== null and $this->getFactionFor($entity)->isAlly($this->getFactionFor($damager))) {
                        $event->setCancelled();
                    }
                    if ($this->getFactionFor($entity) !== null and $this->getFactionFor($damager) !== null) {
                        if (($this->getFactionFor($entity)->isClaimed($entity->x >> 4, $entity->z >> 4, $entity->getLevel()->getName()) and $this->getFactionFor($damager)->isEnemy($this->getFactionFor($entity)) == false) or $this->getFactionFor($damager)->isAlly($this->getFactionFor($entity))) {
                            $event->setCancelled();
                        }
                    }
                }
            }
        }
    }

    /**
     * @param PlayerDeathEvent $event
     */
    public function onDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        $cause = $player->getLastDamageCause();

        if($cause instanceof EntityDamageByEntityEvent){
            if(($damager = $cause->getDamager()) instanceof Player){
                /** @var Player $damager */
                $playerFaction = $this->getFactionFor($player);
                $damagerFaction = $this->getFactionFor($damager);

                $dMember = $this->getMember($damager);
                $dMember->addActivityPoints($this->getConfig()->get("activity-point-gain-kill"));

                if($playerFaction !== null and $damagerFaction !== null){
                    if($damagerFaction->isEnemy($playerFaction)){
                        $amount = $playerFaction->getBalance() * (0.5 / 100);

                        if($amount > 0){
                            EconomyAPI::getInstance()->addMoney($damager, $amount);
                            $damager->sendMessage(Loader::ALERT_GREEN . "Gained +" . $amount . "$ from killing an enemy");
                            $playerFaction->removeBalance($amount);
                        }
                    }
                }
                if($damagerFaction){
                    $damagerFaction->addSTR($this->getConfig()->get("str-gain-kill"));
                    $damagerFaction->broadcastMessage(Loader::ALERT_GREEN . "+" . $this->getConfig()->get("str-gain-kill") . " STR for faction");

                    if($damagerFaction->getDTR() < $this->getConfig()->get("max-dtr")){
                        $damagerFaction->addDTR(1);
                        $damagerFaction->broadcastMessage(Loader::ALERT_GREEN . "+1 DTR for faction");
                    }

                    if($playerFaction !== null){
                        if($playerFaction->getSTR() > $this->getConfig()->get("str-lose-death")){
                            $playerFaction->removeSTR($this->getConfig()->get("str-lose-death"));
                        }else{
                            $playerFaction->removeSTR($this->getFactionFor($player)->getSTR());
                        }
                        $playerFaction->broadcastMessage(Loader::ALERT_RED . "-" . $this->getConfig()->get("str-lose-death") . " STR for faction");

                        if($playerFaction->getDTR() > 1){
                            $playerFaction->removeDTR(1);
                            $playerFaction->broadcastMessage(Loader::ALERT_RED . "-1 DTR for faction");
                        }
                    }
                }
            }
        }
    }

    /**
     * @param ItemSpawnEvent $event
     * @ignoreCancelled false
     */
    public function onItemSpawn(ItemSpawnEvent $event): void {
        $item = $event->getEntity();
        $item->setNameTag(TextFormat::GRAY . $item->getItem()->getName() . TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . " x" . TextFormat::DARK_RED . $item->getItem()->getCount());
        $item->setNameTagAlwaysVisible(true);
    }

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        $cmd = array_shift($args);
        foreach($this->commands as $baseCommand){
            if($baseCommand->getCommand() == $cmd or in_array($cmd, $baseCommand->getAliases())){
                $baseCommand->onRun($sender, $args);

                return true;
            }
        }

        $sender->sendMessage(self::ALERT_RED . "Unknown command, try /faction help for a list of commands");

        return true;
    }
}