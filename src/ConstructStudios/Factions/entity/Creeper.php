<?php
namespace ConstructStudios\Factions\entity;

use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\level\Explosion;

class Creeper extends Living {

    public const NETWORK_ID = self::CREEPER;

    /** @var float */
    public $height = 1.8;

    /** @var float */
    public $width = 0.6;

    /** @var int */
    protected $blowingTimer = 0;

    /**
     * @return string
     */
    public function getName(): string {
        return "Creeper";
    }

    public function initEntity(): void {
        parent::initEntity();

        $this->setMaxHealth(20);
        $this->setHealth(20);
    }

    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool {
        if($this->blowingTimer > 0){
            if(--$this->blowingTimer == 0){
                $exp = new Explosion($this->asPosition(), 3);
                if($this->getLevel() !== $this->getLevel()->getServer()->getDefaultLevel()){
                    $exp->explodeA();
                }
                $exp->explodeB();

                $this->kill();
            }
        }
        return parent::entityBaseTick($tickDiff);
    }

    /**
     * @param EntityDamageEvent $source
     */
    public function attack(EntityDamageEvent $source): void {
        parent::attack($source);

        if($this->blowingTimer == 0){
            $this->blowingTimer = 45;

            $this->setGenericFlag(self::DATA_FLAG_IGNITED, true);
        }
    }

    /**
     * @return array
     */
    public function getDrops(): array {
        return [Item::get(Item::GUNPOWDER, 0, mt_rand(0, 5))];
    }
}