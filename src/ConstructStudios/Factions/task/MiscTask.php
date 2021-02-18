<?php
namespace ConstructStudios\Factions\task;

use pocketmine\entity\Human;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\scheduler\Task;

use ConstructStudios\Factions\Loader;

class MiscTask extends Task {

    /** @var Loader */
    protected $loader;

    /** @var array */
    protected $timer = [];

    /**
     * DTRGainTask constructor.
     * @param Loader $loader
     */
    public function __construct(Loader $loader) {
        $this->loader = $loader;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) {
        $loader = $this->loader;

        foreach($loader->getServer()->getLevels() as $level){
            foreach($level->getChunks() as $chunk){
                if($level->getChunkLoaders($chunk->getX(), $chunk->getZ()) === []){
                    if($loader->getConfig()->get("unload-chunk")){
                        $level->unloadChunk($chunk->getX(), $chunk->getZ());
                    }
                }
            }
            foreach($level->getEntities() as $entity){
                if($entity instanceof Arrow or $entity instanceof ItemEntity or $entity instanceof ExperienceOrb){
                    if(isset($this->timer[$entity->getId()])){
                        unset($this->timer[$entity->getId()]);

                        $entity->flagForDespawn();
                    }else{
                        if($level->getNearestEntity($entity, 10,Human::class) == null){
                            $this->timer[$entity->getId()] = true;
                        }
                    }
                }
            }
        }
    }
}