<?php
namespace ConstructStudios\Factions\utils;

use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

class FactionShopItem {

    /** @var Item */
    protected $item;

    /** @var Item */
    protected $display;

    /** @var float */
    protected $cost = 0.00;

    /** @var string[] */
    protected $commands = [];

    /** @var bool */
    protected $grant = true;

    /**
     * FactionShopItem constructor.
     * @param array $data
     */
    public function __construct(array $data) {
        $this->item = ItemBuilder::buildItem($data["item"], $data["customName"] ?? null, $data["nbt"] ?? null, $data["lore"] ?? [], $data["enchants"] ?? [], true);
        $this->cost = $data["cost"];
        $this->grant = $data["grant-item"];
        $this->commands = $data["commands"];

        $this->display = clone $this->item;
        $this->display->setCustomName(str_replace("\n", "\n", TextFormat::colorize($data["display"])));
        $this->display->setLore(array_merge($this->item->getLore(), [
            " ",
            TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . "COST: " . TextFormat::RESET . TextFormat::GRAY . ScoreboardBuilder::shortNumber($this->cost) . "AP"
        ]));
    }

    /**
     * @return float
     */
    public function getCost(): float {
        return $this->cost;
    }

    /**
     * @return string[]
     */
    public function getCommands(): array {
        return $this->commands;
    }

    /**
     * @return Item
     */
    public function getItem(): Item {
        return $this->item;
    }

    /**
     * @return Item
     */
    public function getDisplay(): Item {
        return $this->display;
    }

    /**
     * @return bool
     */
    public function isGrant(): bool {
        return $this->grant;
    }
}