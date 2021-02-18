<?php
namespace ConstructStudios\Factions\inventory;

use pocketmine\block\Block;
use pocketmine\inventory\BaseInventory;
use pocketmine\inventory\ContainerInventory;
use pocketmine\level\Position;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket as BlockEntityDataPacket;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\Player;

class FactionShopInventory extends ContainerInventory {

    /** @var Position */
    public $holder;

    /** @var int */
    public $page = 0;

    /**
     * FactionShopInventory constructor.
     * @param Position $holder
     */
    public function __construct(Position $holder) {
        $holder->x = (int) $holder->x;
        $holder->y = (int) $holder->y + 2;
        $holder->z = (int) $holder->z;

        parent::__construct($holder, [], null, "");
    }

    /**
     * @return Position
     */
    public function getHolder() {
        return $this->holder;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return "Faction Shop Inventory";
    }

    /**
     * @return int
     */
    public function getDefaultSize(): int {
        return 27;
    }

    /**
     * @return int
     */
    public function getNetworkType(): int {
        return WindowTypes::CONTAINER;
    }

    /**
     * @param Player $who
     */
    public function onOpen(Player $who): void {
        $block = Block::get(Block::CHEST, 0, $this->holder);
        $block->getLevel()->sendBlocks([$who], [$block]);

        $tag = new CompoundTag();
        $tag->setString("id", "Chest");
        $tag->setString("CustomName", "FACTION SHOP");

        $pk = new BlockEntityDataPacket();
        $pk->x = $this->holder->x;
        $pk->y = $this->holder->y;
        $pk->z = $this->holder->z;
        $pk->namedtag = (new NetworkLittleEndianNBTStream())->write($tag);

        $who->sendDataPacket($pk);

        parent::onOpen($who);
    }

    /**
     * @param Player $who
     */
    public function onClose(Player $who): void {
        BaseInventory::onClose($who);

        $this->holder->getLevel()->sendBlocks([$who], [$this->holder->getLevel()->getBlock($this->holder)]);
    }
}