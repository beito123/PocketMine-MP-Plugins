<?php
/*
 * Copyright (c) 2015 beito
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
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

namespace beito\FlowerPot\omake\Note;

use pocketmine\block\Block;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;

use pocketmine\tile\Tile;
use pocketmine\tile\Spawnable;

use beito\FlowerPot\MainClass;

class Note extends Spawnable{

	public function __construct(FullChunk $chunk, Compound $nbt){
		if(!isset($nbt->note)){
			$nbt->note = new Byte("note", 0);
		}
		if(!isset($nbt->powered)){
			$nbt->powered = new Byte("powered", 0);
		}
		parent::__construct($chunk, $nbt);
	}

	public function getNote(){
		return $this->namedtag["note"];
	}

	public function getPowered(){
		return $this->namedtag["powered"] === 1;
	}

	public function setNote($note){
		$this->namedtag->note = new Byte("note", $note);
	}

	public function setPowered($bool){
		$this->namedtag->powered = new Byte("powered", ($bool === true ? 1:0));
	}

	public function getSpawnCompound(){
		return new Compound("", [
			new String("id", MainClass::TILE_NOTE),
			new Int("x", (int) $this->x),
			new Int("y", (int) $this->y),
			new Int("z", (int) $this->z),
			new Byte("note", $this->namedtag["note"]),
			new Byte("powered", $this->namedtag["powered"])
		]);	
	}
}