<?php

declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\Command;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use uhc\UHC;
use function mb_strtolower;

class HealCommand extends BaseCommand
{

	public function __construct(UHC $plugin)
	{
		parent::__construct($plugin, "heal", "Heals a given player", "/heal <player>");
		$this->setPermission("uhc.command.heal");
	}

	public function onExecute(Player $sender, array $args): void
	{
		if (!isset($args[0])) {
			throw new InvalidCommandSyntaxException();
		}

		$player = $this->getPlugin()->getServer()->getPlayer(mb_strtolower($args[0]));
		if ($player !== null) {
			$player->setHealth($player->getMaxHealth());
			$player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
			$sender->sendMessage(TF::RED . "You have healed " . TF::BOLD . TF::AQUA . $player->getDisplayName() . TF::RESET . TF::RED . "!");
			Command::broadcastCommandMessage($sender, "Healed: " . $player->getDisplayName(), false);
		}
	}
}
