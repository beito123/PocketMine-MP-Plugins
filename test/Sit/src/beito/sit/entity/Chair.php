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

namespace beito\sit\entity;

use pocketmine\entity\Entity;
use pocketmine\entity\Item;
use pocketmine\Player;
use pocketmine\Server;

use pocketmine\nbt\tag\ByteTag;

use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\SetEntityLinkPacket;

class Chair extends Entity {

	const NETWORK_ID = -1;

	const SITTING_ACTION_ID = 2;

	const STAND_ACTION_ID = 3;

	public $canCollide = false;

	private $sittingEntity = null;

	protected function initEntity(){
		parent::initEntity();

		if(isset($this->namedtag->remove)){//flag check
			$this->kill();
		}
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Item::NETWORK_ID;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = 0;
		$pk->speedY = 0;
		$pk->speedZ = 0;
		$pk->yaw = 0;
		$pk->pitch = 0;
		$pk->metadata = [
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, 1 << Entity::DATA_FLAG_INVISIBLE],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, ""],
			Entity::DATA_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
			Entity::DATA_NO_AI => [Entity::DATA_TYPE_BYTE, 1]
		];
		$player->dataPacket($pk);

		if($this->sittingEntity !== null){
			$this->sendLinkPacket($player, self::SITTING_ACTION_ID);
		}

		parent::spawnTo($player);
	}

	public function close(){
		if(!$this->closed){
			if($this->sittingEntity !== null){
				$this->standupSittingEntity();
			}
		}
		parent::close();
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->remove = new ByteTag("remove", 1);//remove flag
	}

	//
	
	public function getSittingEntity(){
		return $this->sittingEntity;
	}

	public function sitEntity(Entity $entity){
		if($this->sittingEntity != null){
			return false;
		}

		$this->sittingEntity = $entity;

		$this->sendLinkPacketToAll(self::SITTING_ACTION_ID);
		
		if($this->sittingEntity instanceof Player){
			$this->sendLinkPacketToSittingPlayer(self::SITTING_ACTION_ID);
		}

		return true;
	}

	public function standupSittingEntity(){
		if($this->sittingEntity === null){
			return false;
		}

		$this->sendLinkPacketToAll(self::STAND_ACTION_ID);
		
		if($this->sittingEntity instanceof Player){
			$this->sendLinkPacketToSittingPlayer(self::STAND_ACTION_ID);
		}

		$this->sittingEntity = null;
		return true;
	}

	public function sendLinkPacket(Player $player, $type){
		if($this->sittingEntity === null){
			return false;
		}
		$pk = new SetEntityLinkPacket();
		$pk->from = $this->getId();
		$pk->to = $this->sittingEntity->getId();
		$pk->type = $type;

		$player->dataPacket($pk);
		return true;
	}

	public function sendLinkPacketToSittingPlayer($type){
		if($this->sittingEntity === null or !($this->sittingEntity instanceof Player)){
			return false;
		}

		$pk = new SetEntityLinkPacket();
		$pk->from = $this->getId();
		$pk->to = 0;
		$pk->type = $type;

		$this->sittingEntity->dataPacket($pk);
		return true;
	}

	public function sendLinkPacketToAll($type){
		if($this->sittingEntity === null){
			return false;
		}

		$players = $this->level->getPlayers();
		foreach($players as $player){
			$this->sendLinkPacket($player, $type);
		}
		return true;
	}
}
