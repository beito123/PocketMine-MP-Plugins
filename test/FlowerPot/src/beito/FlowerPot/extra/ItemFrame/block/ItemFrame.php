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

namespace beito\FlowerPot\extra\ItemFrame\block;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\Transparent;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;
use pocketmine\Server;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\String;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\Float;
use pocketmine\tile\Tile;
use pocketmine\math\Vector3;

use beito\FlowerPot\MainClass;
use beito\FlowerPot\extra\ItemFrame\tile\ItemFrame as TileItemFrame;

class ItemFrame extends Transparent {

	protected $id = MainClass::BLOCK_ITEM_FRAME;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getHardness(){
		return 1;
	}

	public function isSolid(){
		return false;
	}

	public function canBeActivated(){
		return true;
	}

	public function getName(){
		return "Item Frame";
	}

	//public function getBoundingBox(){//todo


	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($face >= 2){
			$faces = [
				2 => 3,
				3 => 2,
				4 => 1,
				5 => 4,
			];
			$itemTag = NBT::putItemHelper(Item::get(Item::AIR));
			$itemTag->setName("Item");
			$nbt = new Compound("", [
				new String("id", MainClass::TILE_ITEM_FRAME),
				new Int("x", $block->x),
				new Int("y", $block->y),
				new Int("z", $block->z),
				$itemTag,
				new Float("ItemDropChance", 1),
				new Byte("ItemRotation", 0)
			]);
			$chunk = $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4);
			Tile::createTile("ItemFrame", $chunk, $nbt);

			$this->getLevel()->setBlock($block, Block::get(MainClass::BLOCK_ITEM_FRAME, $faces[$face]), true, true);
			return true;
		}
		return false;
	}

	public function onActivate(Item $item, Player $player = null){
		$tile = $this->level->getTile($this);
		if($tile instanceof TileItemFrame){
			if($tile->getItem()->getId() === Item::AIR){
				$tile->setItem(Item::get($item->getId(), $item->getDamage(), 1));
				$item->setCount($item->getCount() - 1);
			}else{
				$rot = $tile->getItemRotation() + 1;
				$tile->setItemRotation($rot > 8 ? 0:$rot);
			}
			return true;
		}
		return false;
	}

	public function getDrops(Item $item){
		return [[MainClass::ITEM_ITEM_FRAME, 0, 1]];
	}
}