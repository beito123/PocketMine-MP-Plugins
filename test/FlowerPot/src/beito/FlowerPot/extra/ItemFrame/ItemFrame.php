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

namespace beito\FlowerPot\extra\ItemFrame;

use pocketmine\item\Item;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\tile\Spawnable;

use beito\FlowerPot\MainClass;

class ItemFrame extends Spawnable {

	public function __construct(FullChunk $chunk, CompoundTag $nbt){
		if(!isset($nbt->Item) or !($nbt->Item instanceof CompoundTag)){
			$tag = NBT::putItemHelper(Item::get(Item::AIR));
			$tag->setName("Item");
			$nbt->Item = $tag;
		}

		if(!isset($nbt->ItemDropChance)){
			$nbt->ItemDropChance = new FloatTag("ItemDropChance", 1.0);
		}

		if(!isset($nbt->ItemRotation)){
			$nbt->ItemRotation = new ByteTag("ItemRotation", 0);
		}
		
		parent::__construct($chunk, $nbt);
	}

	public function getItem(){
		return NBT::getItemHelper($this->namedtag["Item"]);
	}

	public function setItem(Item $item){
		$tag = NBT::putItemHelper($item);
		$tag->setName("Item");
		$this->namedtag->Item = $tag;

		$this->spawnToAll();

		if($this->chunk){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
		}
	}

	public function getItemDropChance(){
		return $this->namedtag["ItemDropChance"];
	}

	public function setItemDropChance($chance = 1){
		$this->namedtag->ItemDropChance = new FloatTag("ItemDropChance", $chance);
	}

	public function getItemRotation(){
		return $this->namedtag["ItemRotation"];
	}

	public function setItemRotation($rot){
		$this->namedtag->ItemRotation = new ByteTag("ItemRotation", $rot);

		$this->spawnToAll();

		if($this->chunk){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
		}
	}

	public function getSpawnCompound(){
		return new CompoundTag("", [
			new StringTag("id", MainClass::TILE_ITEM_FRAME),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z),
			$this->namedtag["Item"],
			new FloatTag("ItemDropChance", $this->namedtag["ItemDropChance"]),
			new ByteTag("ItemRotation", $this->namedtag["ItemRotation"])
		]);	
	}
}