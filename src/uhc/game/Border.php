<?php

declare(strict_types=1);

namespace uhc\game;

use pocketmine\block\Liquid;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;
use function mt_rand;

class Border
{
	/** @var int */
	private int $size = 1000;
	/** @var World */
	private World $world;
	/** @var int */
	private int $safeX;
	/** @var int */
	private int $safeZ;

	public function __construct(World $world)
	{
		$this->world = $world;
		$this->safeX = $world->getSafeSpawn()->getFloorX();
		$this->safeZ = $world->getSafeSpawn()->getFloorZ();
	}

	public function setSize(int $size): void
	{
		$this->size = $size;
	}

	public function getSize(): int
	{
		return $this->size;
	}

	public function getX(bool $isNegative = false): int
	{
		return $isNegative ? ($this->safeX - $this->size) : ($this->safeX + $this->size);
	}

	public function getZ(bool $isNegative = false): int
	{
		return $isNegative ? ($this->safeZ - $this->size) : ($this->safeZ + $this->size);
	}

	public function isPlayerInsideOfBorder(Player $p): bool
	{
		$playerPos = $p->getPosition();
		if (
			$playerPos->getFloorX() > $this->getX() || $playerPos->getFloorX() < $this->getX(true) ||
			$playerPos->getFloorZ() > $this->getZ() || $playerPos->getFloorZ() < $this->getZ(true)
		) {
			return false;
		}

		return true;
	}

	public function teleportPlayer(Player $p): void
	{
		$playerPos = $p->getPosition();
		$x = mt_rand(5, 20);
		$z = mt_rand(5, 20);
		if ($playerPos->getFloorX() < 0 && $playerPos->getFloorZ() < 0) {
			$pX = $this->getX(true) + $x;
			$pZ = $this->getZ(true) + $z;
		} elseif ($playerPos->getFloorX() > 0 && $playerPos->getFloorZ() > 0) {
			$pX = $this->getX() - $x;
			$pZ = $this->getZ() - $z;
		} elseif ($playerPos->getFloorX() < 0 && $playerPos->getFloorZ() > 0) {
			$pX = $this->getX(true) + $x;
			$pZ = $this->getZ() - $z;
		} else {
			$pX = $this->getX() - $x;
			$pZ = $this->getZ(true) + $z;
		}

		$this->world->orderChunkPopulation($pX >> 4, $pZ >> 4, null)->onCompletion(function () use($p, $pX, $pZ): void
		{
			$p->teleport(new Vector3($pX, $this->world->getHighestBlockAt($pX, $pZ) + 1, $pZ));
		}, function (): void{});
	}

	public function build(): void
	{
		for ($minX = $this->getX(true); $minX < $this->getX(); $minX++) {
			$this->generateBorderWall($minX, $this->getZ());
			$this->generateBorderWall($minX, $this->getZ(true));
		}

		for ($minZ = $this->getZ(true); $minZ < $this->getZ(); $minZ++) {
			$this->generateBorderWall($this->getX(), $minZ);
			$this->generateBorderWall($this->getX(true), $minZ);
		}
	}

	public function generateBorderWall(int $x, int $z) : void
	{
		$this->world->orderChunkPopulation($x >> 4, $z >> 4, null)->onCompletion(function () use($x, $z): void
		{
			$y = 256;
			while ($y > 0){
				$block = $this->world->getBlockAt($x, $y, $z);
				if($block instanceof Liquid || !$block->isTransparent()){
					for ($borderY = $block->getPos()->getFloorY(); $borderY <= $block->getPos()->getFloorY() + 4; $borderY++) {
						$this->world->setBlockAt($x, $borderY, $z, VanillaBlocks::BEDROCK());
					}
					break;
				}

				$y--;
			}
		}, function (): void{});
	}
}
