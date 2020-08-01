<?php

declare(strict_types=1);

namespace uhc\session;

use pocketmine\player\Player;
use uhc\game\team\Team;

class PlayerSession
{
	/** @var Player */
	private $player;
	/** @var int */
	private $eliminations = 0;
	/** @var Team|null */
	private $team = null;

	public function __construct(Player $player)
	{
		$this->player = $player;
	}

	public function getPlayer(): Player
	{
		return $this->player;
	}

	public function addEliminations(int $amount = 1): void
	{
		$this->eliminations += $amount;
	}

	public function getEliminations(): int
	{
		return $this->eliminations;
	}

	public function getTeam(): ?Team
	{
		return $this->team;
	}

	public function isInTeam(): bool
	{
		return $this->team !== null;
	}

	public function addToTeam(Team $team): bool
	{
		if ($team->isLeader($this->player) || $team->addMember($this->getPlayer())) {
			$this->team = $team;
			return true;
		}

		return false;
	}

	public function removeFromTeam(): bool
	{
		if ($this->team->removeMember($this->getPlayer())) {
			$this->team = null;
			return true;
		}

		return false;
	}

	public function isTeamLeader(): bool
	{
		return $this->isInTeam() && $this->team->isLeader($this->getPlayer());
	}

	public function update(Player $player): void
	{
		$this->player = $player;
	}
}