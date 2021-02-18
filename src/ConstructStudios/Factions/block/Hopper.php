<?php
namespace ConstructStudios\Factions\block;

use pocketmine\block\Block;
use pocketmine\block\BlockToolType;
use pocketmine\block\Transparent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use ConstructStudios\Factions\tile\HopperTile;

class Hopper extends Transparent {

    /** @var int */
    protected $id = self::HOPPER_BLOCK;

    /**
     * @param int $meta
     */
    public function __construct(int $meta = 0) {
        $this->meta = $meta;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return "Hopper";
    }

    /**
     * @return bool
     */
    public function canBeActivated(): bool {
        return true;
    }

    /**
     * @return int
     */
    public function getToolType(): int {
        return BlockToolType::TYPE_PICKAXE;
    }

    /**
     * @return float
     */
    public function getHardness(): float {
        return 3.00;
    }

    /**
     * @return float
     */
    public function getBlastResistance(): float {
        return 24.00;
    }

    /**
     * @param Item $item
     * @param Player $player
     * @return bool
     */
    public function onActivate(Item $item, Player $player = null): bool {
        if($player instanceof Player){
            $tile = $this->getLevel()->getTile($this);

            if($tile instanceof HopperTile == false){
                $tile = new HopperTile($this->getLevel(), HopperTile::createNBT($this, null, $item, $player));
                $tile->spawnToAll();
            }
            if($tile instanceof HopperTile){
                $player->addWindow($tile->getInventory());
            }
        }

        return true;
    }

    /**
     * @param Item $item
     * @param Block $replace
     * @param Block $touch
     * @param int $face
     * @param Vector3 $clickPos
     * @param Player $player
     * @return bool
     */
    public function place(Item $item, Block $replace, Block $touch, int $face, Vector3 $clickPos, Player $player = null): bool {
        $m = [
            0 => 0,
            1 => 0,
            2 => 3,
            3 => 2,
            4 => 5,
            5 => 4
        ];

        $this->meta = $m[$face];
        $this->getLevel()->setBlock($this, $this, true, true);

        (new HopperTile($this->getLevel(), HopperTile::createNBT($this, $face, $item, $player)))->spawnToAll();

        return true;
    }

    /**
     * @param Item $item
     * @return array
     */
    public function getDrops(Item $item): array {
        return [
            Item::get(Item::HOPPER)
        ];
    }
}