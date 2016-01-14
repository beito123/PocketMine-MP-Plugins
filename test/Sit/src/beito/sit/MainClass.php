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
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
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
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\math\Vector3;

use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\SetEntityLinkPacket;

use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Float;

use beito\sit\entity\Chair;

class MainClass extends PluginBase implements Listener {

	private $sittingPlayers = array();

	public function onEnable(){
		Entity::registerEntity(Chair::class, true);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onDisable(){
		foreach($this->sittingPlayers as $chair){
			$chair->close();
		}
	}

	public function onDeath(PlayerDeathEvent $event){//死亡時用close
		$entity = $event->getEntity();
		if(isset($this->sittingPlayers[$entity->getName()])){
			$this->sittingPlayers[$entity->getName()]->close();
			unset($this->sittingPlayers[$entity->getName()]);
		}
	}

	public function onDespawn(EntityDespawnEvent $event){//退出時などにChairをcloseするように
		$entity = $event->getEntity();
		if($entity instanceof Player){
			if(isset($this->sittingPlayers[$entity->getName()])){
				$this->sittingPlayers[$entity->getName()]->close();
				unset($this->sittingPlayers[$entity->getName()]);
			}
		}
	}

	public function onInteract(DataPacketReceiveEvent $event){
		$packet = $event->getPacket();
		switch($packet::NETWORK_ID){
			case Info::INTERACT_PACKET:
				$player = $event->getPlayer();

				$action = $packet->action;
				$target = $player->level->getEntity($packet->target);
				if($target instanceof Chair){
					if($action === 2 or $action === 3){
						if($event->getPlayer() == $target->getSittingEntity()){
							$target->standSittingEntity();
						}
						$target->close();
						if(isset($this->sittingPlayers[$player->getName()])){
							$this->sittingPlayers[$player->getName()]->close();
							unset($this->sittingPlayers[$player->getName()]);
						}
					}
				}
				break;
		}
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		switch(strtolower($command->getName())){
			case "sit":
				if(!($sender instanceof Player)){
					$sender->sendMessage("ゲーム内で実行して下さい。");
					break;
				}

				if(isset($this->sittingPlayers[$sender->getName()])){
					$this->sittingPlayers[$sender->getName()]->close();
					unset($this->sittingPlayers[$sender->getName()]);
				}

				$x = $sender->getX();
				$y = $sender->getY();
				$z = $sender->getZ();
				if($sender->getLevel()->getBlock($sender->getSide(Vector3::SIDE_DOWN)) instanceof Stair){
					$x = ((int) $x) + 0.5;
					$y = (((int) $y) - 1) + 0.2;
					$z = ((int) $z) + 0.5;
				}else{
					$y -= 0.3;
				}

				$entity = Entity::createEntity("Chair", $sender->chunk, new Compound("", [
					"Pos" => new Enum("Pos", [
						new Double("", $x),
						new Double("", $y),
						new Double("", $z)
					]),
					"Motion" => new Enum("Motion", [
						new Double("", 0),
						new Double("", 0),
						new Double("", 0)
					]),
					"Rotation" => new Enum("Rotation", [
						new Float("", 0),
						new Float("", 0)
					])
				]));
				$entity->spawnToAll();

				$entity->sitEntity($sender);

				$this->sittingPlayers[$sender->getName()] = $entity;
				break;
		}
		return true;
	}
}