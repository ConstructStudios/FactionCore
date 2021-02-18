<?php
namespace ConstructStudios\Factions\level\generator;


use pocketmine\block\Block;
use pocketmine\block\Stone;
use pocketmine\level\biome\Biome;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\noise\Simplex;
use pocketmine\level\generator\normal\Normal;

use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\populator\Ore;
use pocketmine\level\generator\populator\Populator;
use pocketmine\level\Level;
use pocketmine\utils\Random;
use ConstructStudios\Factions\level\populator\Cave;
use ConstructStudios\Factions\level\populator\Lake;

class FactionGenerator extends Normal {

    const IGNORE_OVERRIDE = [
        Block::STONE,
        Block::GRAVEL,
        Block::BEDROCK,
        Block::DIAMOND_ORE,
        Block::GOLD_ORE,
        Block::LAPIS_ORE,
        Block::REDSTONE_ORE,
        Block::IRON_ORE,
        Block::COAL_ORE,
        Block::WATER,
        Block::STILL_WATER
    ];

    /** @var Simplex */
    protected $noiseBase;

    /** @var BiomeSelector */
    protected $selector;

    /** @var Level */
    protected $level;

    /** @var Random */
    protected $random;

    /** @var Populator[] */
    protected $populators = [];

    /** @var Populator[] */
    protected $generationPopulators = [];

    /**
     * @param int $chunkX
     * @param int $chunkZ
     */
    public function populateChunk($chunkX, $chunkZ): void {
        $this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
        foreach($this->populators as $populator){
            $populator->populate($this->level, $chunkX, $chunkZ, $this->random);
        }

        for($x = $chunkX; $x < $chunkX + 16; $x++) for($z = $chunkZ; $z < $chunkZ + 16; $z++) for($y = 1; $y < 11; $y++){
            if(!in_array($this->level->getBlockIdAt($x, $y, $z), self::IGNORE_OVERRIDE)){
                $this->level->setBlockIdAt($x, $y, $z, Block::LAVA);
            }
        }

        $chunk = $this->level->getChunk($chunkX, $chunkZ);
        $biome = Biome::getBiome($chunk->getBiomeId(7, 7));
        $biome->populateChunk($this->level, $chunkX, $chunkZ, $this->random);
    }

    /**
     * @param ChunkManager $level
     * @param Random $random
     */
    public function init(ChunkManager $level, Random $random): void {
        parent::init($level, $random);

        $this->random->setSeed($this->level->getSeed());
        $this->noiseBase = new Simplex($this->random, 8, 1 / 4, 1 / 64);
        $this->random->setSeed($this->level->getSeed());
        $this->selector = new BiomeSelector($this->random);

        $this->selector->recalculate();

        $cave = new Cave();
        $this->generationPopulators[] = $cave;

        $ores = new Ore();
        $ores->setOreTypes([
            new OreType(Block::get(Block::STONE, Stone::GRANITE), 10, 33, 0, 80),
            new OreType(Block::get(Block::STONE, Stone::DIORITE), 10, 33, 0, 80),
            new OreType(Block::get(Block::STONE, Stone::ANDESITE), 10, 33, 0, 80)
        ]);
        $this->populators[] = $ores;
        $this->populators[] = new Cave();
    }
}