<?php
/*
 * Copyright (c) 2015 beito
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

namespace beito\FlowerPot;

use pocketmine\block\Block;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\Short;
use pocketmine\nbt\tag\String;

use pocketmine\tile\Tile;
use pocketmine\tile\Spawnable;

class FlowerPot extends Spawnable{

	public function __construct(FullChunk $chunk, Compound $nbt){
		if(isset($nbt->item)){
			$nbt->Item = new Short("Item", $nbt["item"]);
			unset($nbt["item"]);
		}elseif(isset($nbt->Item) and $nbt->Item->getType() === NBT::TAG_Int){
 			$nbt->Item = new Short("Item", (int) $nbt["Item"]);
		}

		if(isset($nbt->data)){
			$nbt->Data = new Int("Data", $nbt["data"]);
			unset($nbt["data"]);
		}elseif(isset($nbt->mData)){
			$nbt->Data = new Int("Data", $nbt["mData"]);
		}

		if(!isset($nbt->Item)){
			$nbt->Item = new Short("Item", 0);
		}
		if(!isset($nbt->Data)){
			$nbt->Data = new Int("Data", 0);
		}
		parent::__construct($chunk, $nbt);
	}

	public function getFlowerPotItem(){
		return (int) $this->namedtag["Item"];
	}

	public function getFlowerPotData(){
		return (int) $this->namedtag["Data"];
	}

	/**
	 * Set flower data to FlowerPot
	 * @param int $item itemid
	 * @param int $data metadata
	 */
	public function setFlowerPotData($item, $data){
		$this->namedtag->Item = new Short("Item", (int) $item);
		$this->namedtag->Data = new Int("Data", (int) $data);
		$this->spawnToAll();

		if($this->chunk){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
		}
		return true;
	}

	public function getSpawnCompound(){
		return new Compound("", [
			new String("id", Tile::FLOWER_POT),
			new Int("x", (int) $this->x),
			new Int("y", (int) $this->y),
			new Int("z", (int) $this->z),
			new Short("item", (int) $this->namedtag["Item"]),
			new Int("mData", (int) $this->namedtag["Data"])
		]);	
	}
}