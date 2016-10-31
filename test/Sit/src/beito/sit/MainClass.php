<?php
/*
 * Copyright (c) 2016 beito
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
*/

namespace beito\sit;

use pocketmine\block\Stair;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\math\Vector3;

use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\SetEntityLinkPacket;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;

use beito\sit\entity\Chair;

class MainClass extends PluginBase implements Listener {

	private $usingChairs = array();

	public function onEnable() {
		Entity::registerEntity(Chair::class, true);

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onDisable() {
		$this->closeAllChairs();
	}

	public function onDeath(PlayerDeathEvent $event) {//for dead
		$this->closeOldChair($event->getEntity());
	}

	public function onDespawn(EntityDespawnEvent $event) {//close for teleport and quit from server
		$entity = $event->getEntity();
		if($entity instanceof Player){
			$this->closeOldChair($entity);
		}
	}

	public function onBedEnter(PlayerBedEnterEvent $event) {
		$this->closeOldChair($event->getPlayer());
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event){
		$packet = $event->getPacket();
		if($event->getPacket()->pid() === Info::INTERACT_PACKET){
			$packet = $event->getPacket();
			$player = $event->getPlayer();
			
			$target = $player->getLevel()->getEntity($packet->target);
			if($target instanceof Chair){
				$action = $packet->action;
				if($action === 2 or $action === 3){
					$target->standupSittingEntity();
					$target->close();
				}
			}
		}
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		switch(strtolower($command->getName())){
			case "sit":
				if(!($sender instanceof Player)){
					$sender->sendMessage("Please run in the game.");
					break;
				}

				if($sender->isSleeping()){
					$sender->stopSleep();
				}

				$this->closeOldChair($sender);

				$x = $sender->getX();
				$y = $sender->getY();
				$z = $sender->getZ();
				
				if(($block = $sender->getLevel()->getBlock($sender->getSide(Vector3::SIDE_DOWN)) or 
					$block = $sender->getLevel()->getBlock($sender))
						and $block instanceof Stair and $block->getDamage() < 0x04) {
					$d = [
						0 => [-0.2, 0],
						1 => [0.2, 0],
						2 => [0, -0.2],
						3 => [0, 0.2]
					];

					$x = $block->getX() + 0.5 + $d[$block->getDamage()][0];//center of block
					$z = $block->getZ() + 0.5 + $d[$block->getDamage()][1];

					$y = $block->getY() + 1.6;

					echo $block->getDamage();
				}else{
					$y += 1.13;//fix pos
				}

				$entity = Entity::createEntity("Chair", $sender->chunk, new CompoundTag("", [
					"Pos" => new ListTag("Pos", [
						new DoubleTag("", $x),
						new DoubleTag("", $y),
						new DoubleTag("", $z)
					]),
					"Motion" => new ListTag("Motion", [
						new DoubleTag("", 0),
						new DoubleTag("", 0),
						new DoubleTag("", 0)
					]),
					"Rotation" => new ListTag("Rotation", [
						new FloatTag("", 0),
						new FloatTag("", 0)
					])
				]));
				$entity->spawnToAll();

				$entity->sitEntity($sender);

				$sender->sendTip("Stand up by jumping");

				$this->usingChairs[$sender->getName()] = $entity;
				break;
		}
		return true;
	}

	public function closeOldChair(Player $player){
		if(isset($this->usingChairs[$player->getName()])){
			$this->usingChairs[$player->getName()]->close();
			unset($this->usingChairs[$player->getName()]);
		}
	}

	public function closeAllChairs() {
		foreach($this->usingChairs as $chair) {
			$chair->close();
		}
	}
}
