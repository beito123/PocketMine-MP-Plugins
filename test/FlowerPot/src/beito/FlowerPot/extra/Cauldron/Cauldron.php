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

namespace beito\FlowerPot\extra\Cauldron;

use pocketmine\block\Block;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\EnumTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\StringTag;

use pocketmine\tile\Tile;
use pocketmine\tile\Spawnable;

use pocketmine\Server;

use beito\FlowerPot\MainClass;

class Cauldron extends Spawnable{

	public function __construct(FullChunk $chunk, CompoundTag $nbt){
		if(!isset($nbt->PotionId)){
			$nbt->PotionId = new ShortTag("PotionId", 0xffff);
		}
		if(!isset($nbt->SplashPotion)){
			$nbt->SplashPotion = new ByteTag("SplashPotion", 0);
		}
		if(!isset($nbt->Items) or !($nbt->Items instanceof EnumTag)){
			$nbt->Items = new EnumTag("Items", []);
		}
		if(!isset($nbt->CustomColor)){
			$nbt->CustomColor = new IntTag("CustomColor", 0xffffffff);//bgr??rgba???
		}
		parent::__construct($chunk, $nbt);
	}

	public function getPotionId(){
		return $this->namedtag["PotionId"];
	}

	public function setPotionId($potionId){
		$this->namedtag->PotionId = new ShortTag("PotionId", $potionId);
	}

	public function getSplashPotion(){
		return ($this->namedtag["SplashPotion"] == 1);
	}

	public function setSplashPotion($bool){
		$this->namedtag->SplashPotion = new ShortTag("SplashPotion", ($bool == true) ? 1:0);
	}

	public function getCustomColor(){//umm...
		$color = $this->namedtag["CustomColor"];
		return [
			($color >> 8)&0xff,//r
			($color >> 16)&0xff,//g
			($color >> 24)&0xff//b
		];
	}

	public function setCustomColor($r, $g, $b){//umm..
		$color = ($b << 24 | $g << 16 | $r << 8 | 0xff) & 0xffffffff;
		$this->namedtag->CustomColor = new IntTag("CustomColor", $color);
	}

	public function getSpawnCompound(){
		$nbt = new CompoundTag("", [
			new StringTag("id", MainClass::TILE_CAULDRON),
			new IntTag("x", (Int) $this->x),
			new IntTag("y", (Int) $this->y),
			new IntTag("z", (Int) $this->z),
			new ShortTag("PotionId", $this->namedtag["PotionId"]),
			new ByteTag("SplashPotion", $this->namedtag["SplashPotion"]),
			new EnumTag("Items", $this->namedtag["Items"])
		]);

		if($this->namedtag["PotionId"] & 0xffff and !($this->namedtag["CustomColor"] & 0xffffffff)){
			$nbt->CustomColor = $this->namedtag->CustomColor;
		}
		return $nbt;
	}
}