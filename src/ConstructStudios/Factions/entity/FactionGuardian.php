<?php
namespace ConstructStudios\Factions\entity;

use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ConstructStudios\Factions\faction\Faction;
use ConstructStudios\Factions\Loader;

class FactionGuardian extends Living {

    public const NETWORK_ID = self::IRON_GOLEM;

    /** @var float */
    public $height =  2.7;

    /** @var float */
    public $width = 1.4;

    /** @var int */
    protected $gravity = 0.08;

    /** @var float */
    protected $stepHeight = 1.2;

    /** @var int */
    protected $maxDeadTicks = 20;

    /**
     * @return string
     */
    public function getName(): string {
        return "Faction Guardian";
    }

    protected function initEntity(): void {
        parent::initEntity();

        $this->setMaxHealth(100);
        $this->setHealth(100);
        $this->setNameTagVisible(true);
    }

    /**
     * FactionGuardian constructor.
     * @param Level $level
     * @param CompoundTag $nbt
     */
    public function __construct(Level $level, CompoundTag $nbt) {
        parent::__construct($level, $nbt);

        if($nbt->hasTag("Faction", StringTag::class) == false){
            $this->flagForDespawn();
        }
    }

    /**
     * @param EntityDamageEvent $source
     */
    public function attack(EntityDamageEvent $source): void {

        parent::attack($source);
    }

    public function kill(): void {
        parent::kill();

        $this->getFaction()->setGuardians($this->getFaction()->getGuardians() - 1);
        for($i = 0; $i < 25; $i++){
            $this->getLevel()->dropExperience($this->add(lcg_value(), lcg_value(), lcg_value()), mt_rand(1, 15));
        }
    }

    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool {
        if(($faction = $this->getFaction()) == null){
            $this->flagForDespawn();

            return true;
        }

        $this->setNameTag($this->getName() . "\n" . TextFormat::RED . "HP " . TextFormat::WHITE . $this->getHealth());

        if($this->getTargetEntity() == null){
            $this->motion->setComponents(0, $this->motion->y, 0);

            foreach($this->getLevel()->getNearbyEntities($this->getBoundingBox()->expandedCopy(16, 8, 16), $this) as $entity){
                if($entity instanceof Player){
                    if(($fac = Loader::getInstance()->getFactionFor($entity)) !== null and $this->getFaction()->isAlly($fac) or $this->getFaction()->isMember(Loader::getInstance()->getMember($entity))){
                        continue;
                    }

                    $this->setTargetEntity($entity);
                }
            }
        }elseif(($this->getTargetEntity()->isAlive() == false or $this->distance($this->getTargetEntity()) > 16) and $this->getTargetEntity() !== null){
            $this->setTargetEntity(null);
        }
        if($this->getHealth() < $this->getMaxHealth()){
            $this->setHealth($this->getHealth() + mt_rand(0, 1));
        }

        if(($target = $this->getTargetEntity()) !== null){
            $this->lookAt($target);

            if($this->distance($target) <= 1){
                $target->attack(new EntityDamageByEntityEvent($this, $target, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, mt_rand(5, 12), [], 1.2));
            }else{
                $x = $target->x - $this->x;
                $z = $target->z - $this->z;
                $diff = abs($x) + abs($z);

                $mx = 1.4 * 0.20 * ($x / $diff);
                $mz = 1.4 * 0.20 * ($z / $diff);

                $this->motion->setComponents($mx, $this->motion->y, $mz);
            }
        }

        return parent::entityBaseTick($tickDiff);
    }

    /**
     * @return null|Faction
     */
    public function getFaction(): ?Faction {
        return Loader::getInstance()->getFaction($this->namedtag->getString("Faction"));
    }
}