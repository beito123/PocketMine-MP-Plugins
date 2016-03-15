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
use pocketmine\nbt\tag\ListTag;
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
		if(!isset($nbt->Items) or !($nbt->Items instanceof ListTag)){
			$nbt->Items = new ListTag("Items", []);
		}
		if(!isset($nbt->CustomColor)){
			$nbt->CustomColor = new IntTag("CustomColor", 0xffffffff);//rgb
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

	public function getCustomColor(){//
		$color = $this->namedtag["CustomColor"];
		$green = ($color >> 8)&0xff;
		$red = ($color >> 16)&0xff;
		$blue = ($color)&0xff;
		return Color::getRGB($red, $green, $blue);
	}

	public function getCustomColorRed(){
		return ($this->namedtag["CustomColor"] >> 16)&0xff;
	}

	public function getCustomColorGreen(){
		return ($this->namedtag["CustomColor"] >> 8)&0xff;
	}

	public function getCustomColorBlue(){
		return ($this->namedtag["CustomColor"])&0xff;
	}

	public function getCustomColorPadding(){
		return ($this->namedtag["CustomColor"] >> 24)&0xff;
	}

	public function setCustomColor($r, $g = 0xff, $b = 0xff, $padding = 0xff){
		if($r instanceof Color){
			$c = $r;
			$r = $c->getRed();
			$g = $c->getGreen();
			$b = $c->getBlue();
		}
		$color = ($padding << 24 | $r << 16 | $g << 8 | $b) & 0xffffffff;// padding?(8bit), red(8bit), green(8bit), blue(8bit)
		$this->namedtag->CustomColor = new IntTag("CustomColor", $color);

		$this->spawnToAll();
		if($this->chunk){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
		}
	}

	public function getSpawnCompound(){
		$nbt = new CompoundTag("", [
			new StringTag("id", MainClass::TILE_CAULDRON),
			new IntTag("x", (Int) $this->x),
			new IntTag("y", (Int) $this->y),
			new IntTag("z", (Int) $this->z),
			new ShortTag("PotionId", $this->namedtag["PotionId"]),
			new ByteTag("SplashPotion", $this->namedtag["SplashPotion"]),
			new ListTag("Items", $this->namedtag["Items"])//unused?
		]);

		if($this->getPotionId() === 0xffff and $this->getCustomColorPadding() !== 0x00){//todo: fix conditions
			$nbt->CustomColor = $this->namedtag->CustomColor;
			$nbt->CustomColor = new IntTag("CustomColor", $this->namedtag["CustomColor"]);
		}
		return $nbt;
	}
}