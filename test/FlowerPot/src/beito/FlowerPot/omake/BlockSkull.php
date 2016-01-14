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

namespace beito\FlowerPot\omake;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\Transparent;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;
use pocketmine\Server;

use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\tile\Tile;
use pocketmine\math\Vector3;

use beito\FlowerPot\MainClass;

class BlockSkull extends Transparent{

	protected $id = MainClass::BLOCK_SKULL;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getHardness(){
		return 1;
	}

	public function isSolid(){
		return false;
	}

	public function getName(){
		return "Mob Head";
	}

	public function getBoundingBox(){//todo fix
		return new AxisAlignedBB(
			$this->x - 0.75,
			$this->y - 0.5,
			$this->z - 0.75,
			$this->x + 0.75,
			$this->y + 0.5,
			$this->z + 0.75
		);
	}


	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($face !== 0){
			$this->getLevel()->setBlock($block, Block::get(MainClass::BLOCK_SKULL, 0), true, true);
			$nbt = new Compound("", [
				new String("id", Tile::SKULL),
				new Int("x", $block->x),
				new Int("y", $block->y),
				new Int("z", $block->z),
				new Byte("SkullType", $item->getDamage()),
				new Byte("Rot", floor(($player->yaw * 16 / 360) + 0.5) & 0x0F),
			]);
			$chunk = $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4);
			$pot = Tile::createTile("Skull", $chunk, $nbt);

			$this->getLevel()->setBlock($block, Block::get(MainClass::BLOCK_SKULL, $face), true, true);
			return true;
		}
		return false;
	}

	public function onBreak(Item $item){
		$this->getLevel()->setBlock($this, new Air(), true, true, true);
		return true;
	}

	public function getDrops(Item $item){
		if(($tile = $this->getLevel()->getTile($this)) instanceof Skull){
			return [[MainClass::ITEM_SKULL, $tile->getSkullType(), 1]];
		}
		return [[MainClass::ITEM_SKULL, 0, 1]];
	}
}
