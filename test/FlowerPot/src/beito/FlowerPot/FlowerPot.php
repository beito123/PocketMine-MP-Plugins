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
		if(!isset($nbt->item)){
			$nbt->item = new Short("item", 0);
		}
		if(!isset($nbt->data)){
			$nbt->data = new Int("data", 0);
		}
		parent::__construct($chunk, $nbt);
	}

	public function getFlowerPotItem(){
		return $this->namedtag["item"];
	}

	public function getFlowerPotData(){
		return $this->namedtag["data"];
	}

	/**
	 * 植木鉢(FlowerPot)にデータを設定します
	 * @param int $item アイテムID
	 * @param int $data メタ値
	 */
	public function setFlowerPotData($item, $data){
		$this->namedtag->item = new Short("item", (int) $item);
		$this->namedtag->data = new Int("data", (int) $data);
		$this->spawnToAll();

		if($this->chunk){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());

			$block = $this->level->getBlock($this);
			if($block->getId() === MainClass::BLOCK_FLOWER_POT){
				$this->level->setBlock($this, Block::get(MainClass::BLOCK_FLOWER_POT, ($block->getDamage() === 0 ? 1:0)), true);//bad hack...
			}
		}
		return true;
	}

	public function getSpawnCompound(){
		return new Compound("", [
			new String("id", Tile::FLOWER_POT),
			new Int("x", (int) $this->x),
			new Int("y", (int) $this->y),
			new Int("z", (int) $this->z),
			new Short("item", (int) $this->namedtag["item"]),
			new Int("data", (int) $this->namedtag["data"])
		]);	
	}
}