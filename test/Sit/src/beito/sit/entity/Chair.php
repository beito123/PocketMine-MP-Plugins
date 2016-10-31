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

	const ACTION_STTING = 2;

	const ACTION_STANDING = 3;

	private $sittingEntity = null;

	protected $dataProperties = [
		self::DATA_FLAGS => [self::DATA_TYPE_LONG, 
			1 << Entity::DATA_FLAG_INVISIBLE |
			1 << Entity::DATA_FLAG_IMMOBILE],
		self::DATA_NAMETAG => [self::DATA_TYPE_STRING, ""],
		self::DATA_LEAD_HOLDER_EID => [self::DATA_TYPE_LONG, -1],
		self::DATA_SCALE => [self::DATA_TYPE_FLOAT, 1],
		self::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0.0],
		self::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0.0],
		self::DATA_URL_TAG => [Entity::DATA_TYPE_STRING, "https://exmaple.com/"]
	];

	public $keepMovement = true;

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
		$flags = 0;
		
		$pk->metadata = $this->dataProperties;

		$player->dataPacket($pk);

		if($this->sittingEntity !== null){
			$this->sendLinkPacket($player, self::ACTION_STTING);
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

	
	public function checkBlockCollision() {
		return;
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

		$this->sendLinkPacketToAll(self::ACTION_STTING);
		
		if($this->sittingEntity instanceof Player){
			$this->sendLinkPacketToSittingPlayer(self::ACTION_STTING);
		}

		return true;
	}

	public function standupSittingEntity(){
		if($this->sittingEntity === null){
			return false;
		}

		$this->sendLinkPacketToAll(self::ACTION_STANDING);
		
		if($this->sittingEntity instanceof Player){
			$this->sendLinkPacketToSittingPlayer(self::ACTION_STANDING);
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
