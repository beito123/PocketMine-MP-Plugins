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

namespace beito\FlowerPot\extra\Skull;

use pocketmine\block\Block;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\StringTag;

use pocketmine\tile\Tile;
use pocketmine\tile\Spawnable;

use pocketmine\Server;

class Skull extends Spawnable{

	public function __construct(FullChunk $chunk, CompoundTag $nbt){
		if(!isset($nbt->SkullType)){
			$nbt->SkullType = new ByteTag("SkullType", 0);
		}
		if(!isset($nbt->Rot)){
			$nbt->Rot = new ByteTag("Rot", 0);
		}
		parent::__construct($chunk, $nbt);
	}

	public function getSkullType(){
		return $this->namedtag["SkullType"];
	}

	public function setSkullType($type){
		$this->namedtag->SkullType = new ByteTag("SkullType", $type);
	}

	public function getSkullRot(){
		return $this->namedtag["Rot"];
	}

	public function setSkullRot($rot){
		$this->namedtag->Rot = new ByteTag("Rot", $rot);
	}

	public function getSpawnCompound(){
		return new CompoundTag("", [
			new StringTag("id", Tile::SKULL),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z),
			new ByteTag("SkullType", (int) $this->namedtag["SkullType"]),
			new ByteTag("Rot", (int) $this->namedtag["Rot"])
		]);
	}
}