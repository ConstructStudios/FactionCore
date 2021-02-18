<?php
namespace ConstructStudios\Factions\tile;

use pocketmine\entity\object\ItemEntity;
use pocketmine\event\entity\ItemSpawnEvent;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;
use pocketmine\tile\Container;
use pocketmine\tile\Nameable;
use pocketmine\tile\Spawnable;
use ConstructStudios\Factions\inventory\HopperInventory;

class HopperTile extends Spawnable implements Container, Nameable {
    use \pocketmine\tile\ContainerTrait;
    use \pocketmine\tile\NameableTrait;

    /** @var HopperInventory */
    protected $inventory;

    /** @var AxisAlignedBB */
    protected $bb;

    /** @var int */
    protected $delay = 16;

    /**
     * @param CompoundTag $nbt
     */
    public function readSaveData(CompoundTag $nbt): void {
        $this->loadName($nbt);
        $this->inventory = new HopperInventory($this);

        $this->loadItems($nbt);
        $this->scheduleUpdate();
    }

    /**
     * @return string
     */
    public function getDefaultName(): string {
        return "Item Hopper";
    }

    /**
     * @return int
     */
    public function getSize(): int {
        return 5;
    }

    /**
     * @return HopperInventory
     */
    public function getInventory() {
        return $this->inventory;
    }

    /**
     * @return HopperInventory
     */
    public function getRealInventory() {
        return $this->inventory;
    }

    public function close(): void {
        $this->getInventory()->removeAllViewers();

        parent::close();
    }

    /**
     * @param CompoundTag $nbt
     */
    public function addAdditionalSpawnData(CompoundTag $nbt): void {
        $nbt->setString("CustomName", $this->getName());
    }

    /**
     * @param CompoundTag $nbt
     * @param Vector3 $pos
     * @param int|null $face
     * @param null|Item $item
     * @param null|Player $player
     */
    public static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null): void {
        $nbt->setTag(new ListTag("Items", [], NBT::TAG_Compound));

        if($item !== null and $item->hasCustomName()){
            $nbt->setString("CustomName", $item->getCustomName());
        }
    }

    /**
     * @param CompoundTag $nbt
     */
    public function writeSaveData(CompoundTag $nbt): void {
        $this->saveName($nbt);
        $this->saveItems($nbt);
    }

    /**
     * @return string
     */
    public static function getSaveId(): string {
        return "Hopper";
    }

    /**
     * @return bool
     * @throws \ReflectionException
     */
    public function onUpdate(): bool {
        if(($this->getLevel()->getServer()->getTick() % $this->delay) !== 0 and $this->getLevel()->getServer()->getTicksPerSecond() < 18){
            return true;
        }

        $inv = $this->getInventory();
        $thisHopper = $this->getBlock();

        if($this->bb == null){
            $this->bb = clone $thisHopper->getBoundingBox();
            $this->bb->maxY += 0.75;
        }
        $bb = $this->bb;

        foreach($this->getLevel()->getNearbyEntities($bb) as $itemEn){
            if($itemEn instanceof ItemEntity){
                if($itemEn->isFlaggedForDespawn()){
                    continue;
                }
                if($itemEn->getItem()->isNull()){
                    $itemEn->flagForDespawn();
                    continue;
                }

                $add = clone $itemEn->getItem();
                $add->setCount(1);

                if($inv->canAddItem($add)){
                    $inv->addItem($add);
                    $itemEn->getItem()->setCount($itemEn->getItem()->getCount() - 1);
                    $itemEn->sendData($itemEn->getViewers());

                    (new ItemSpawnEvent($itemEn))->call();

                    if($itemEn->getItem()->getCount() <= 0){
                        $itemEn->flagForDespawn();
                    }

                    break;
                }
            }
        }

        if(($up = $this->getLevel()->getTileAt($this->x, $this->y + 1, $this->z)) instanceof Container){
            /** @var Container $up */
            if(($k = $this->getFirstOccupied($up)) !== -1){
                $sucking = clone $up->getInventory()->getItem($k);
                $sucking->setCount(1);

                if($inv->canAddItem($sucking)){
                    $inv->addItem($sucking);
                    $up->getInventory()->removeItem($sucking);
                }
            }
        }

        $pointing = $thisHopper->getSide($thisHopper->getDamage());

        if(($tr = $this->getLevel()->getTileAt($pointing->x, $pointing->y, $pointing->z)) instanceof Container){
            /** @var Container $tr */
            if(($k = $this->getFirstOccupied($this)) !== -1){
                $sending = clone $inv->getItem($k);
                $sending->setCount(1);

                if($tr->getInventory()->canAddItem($sending)){
                    $inv->removeItem($sending);
                    $tr->getInventory()->addItem($sending);
                }
            }
        }


        return true;
    }

    /**
     * @param Container $tile
     * @return int
     */

    public function getFirstOccupied(Container $tile): int {
        try{
            foreach($tile->getInventory()->getContents(true) as $key => $item){
                if($item->isNull() == false){
                    return $key;
                }
            }

            return -1;
        }catch(\Throwable $e){
            return -1;
        }
    }
}
