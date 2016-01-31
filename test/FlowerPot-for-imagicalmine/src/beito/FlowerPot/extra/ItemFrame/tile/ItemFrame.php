<?php
/*
 * Copyright (c) 2015-2016 beito
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

namespace beito\FlowerPot\extra\ItemFrame\tile;

use pocketmine\block\Block;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\nbt\tag\Float;

use pocketmine\item\Item;
use pocketmine\tile\Tile;
use pocketmine\tile\Spawnable;

use beito\FlowerPot\MainClass;

class ItemFrame extends Spawnable {

	public function __construct(FullChunk $chunk, Compound $nbt){
		if(!isset($nbt->Item) or !($nbt->Item instanceof Compound)){
			$item = NBT::putItemHelper(Item::get(Item::AIR));
			$item->setName("Item");
			$nbt->Item = $item;
		}

		if(!isset($nbt->ItemDropChance)){
			$nbt->ItemDropChance = new Float("ItemDropChance", 1);
		}

		if(!isset($nbt->ItemRotation)){
			$nbt->ItemRotation = new Byte("ItemRotation", 0);
		}
		
		parent::__construct($chunk, $nbt);
	}

	public function getItem(){
		return NBT::getItemHelper($this->namedtag["Item"]);
	}

	public function setItem(Item $item){
		$item = NBT::putItemHelper($item);
		$item->setName("Item");
		$this->namedtag->Item = $item;

		$this->spawnToAll();

		if($this->chunk){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
		}
	}

	public function getItemDropChance(){
		return $this->namedtag["ItemDropChance"];
	}

	public function setItemDropChance($probability = 1){
		$this->namedtag->ItemDropChance = new Float("ItemDropChance", $probability);
	}

	public function getItemRotation(){
		return $this->namedtag["ItemRotation"];
	}

	public function setItemRotation($rot){
		$this->namedtag->ItemRotation = new Byte("ItemRotation", $rot);

		$this->spawnToAll();

		if($this->chunk){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
		}
	}

	public function getSpawnCompound(){
		return new Compound("", [
			new String("id", MainClass::TILE_ITEM_FRAME),
			new Int("x", (int) $this->x),
			new Int("y", (int) $this->y),
			new Int("z", (int) $this->z),
			$this->namedtag["Item"],
			new Float("ItemDropChance", $this->namedtag["ItemDropChance"]),
			new Byte("ItemRotation", $this->namedtag["ItemRotation"])
		]);	
	}
}