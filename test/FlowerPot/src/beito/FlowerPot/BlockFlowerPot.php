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

namespace beito\FlowerPot;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\Transparent;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;
use pocketmine\Server;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\tile\Tile;
use pocketmine\math\Vector3;

class BlockFlowerPot extends Transparent{

	protected $id = MainClass::BLOCK_FLOWER_POT;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function canBeActivated() : bool{
		return true;
	}

	public function getHardness(){
		return 0;
	}

	public function isSolid(){
		return false;
	}

	public function getName() : string{
		return "Flower Pot";
	}

	public function getBoundingBox(){//Thanks to thebigsmileXD!
		return new AxisAlignedBB(
			$this->x - 0.6875,
			$this->y - 0.375,
			$this->z - 0.6875,
			$this->x + 0.6875,
			$this->y + 0.375,
			$this->z + 0.6875
		);
	}


	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($this->getSide(Vector3::SIDE_DOWN)->isTransparent() === false){
			$this->getLevel()->setBlock($block, $this, true, true);
			$nbt = new CompoundTag("", [
				new StringTag("id", Tile::FLOWER_POT),
				new IntTag("x", $block->x),
				new IntTag("y", $block->y),
				new IntTag("z", $block->z),
				new IntTag("item", 0),
				new IntTag("data", 0),
			]);
			$pot = Tile::createTile("FlowerPot", $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);
			return true;
		}
		return false;
	}

	public function onBreak(Item $item){
		$this->getLevel()->setBlock($this, new Air(), true, true, true);
		return true;
	}

	public function onActivate(Item $item, Player $player = null){
		$tile = $this->getLevel()->getTile($this);
		if($tile instanceof FlowerPot){
			if($tile->getFlowerPotItem() === Item::AIR){
				switch($item->getId()){
					case Item::TALL_GRASS:
						if($item->getDamage() === 1){
							break;
						}
					case Item::SAPLING:
					case Item::DEAD_BUSH:
					case Item::DANDELION:
					case Item::RED_FLOWER:
					case Item::BROWN_MUSHROOM:
					case Item::RED_MUSHROOM:
					case Item::CACTUS:
						$tile->setFlowerPotData($item->getId(), $item->getDamage());
						$this->getLevel()->setBlock($this, Block::get($this->id, ($this->meta === 1) ? 0:1), true, true);//bad code...
						if($player->isSurvival()){
							//$item->setCount($item->getCount() - 1);
							$item->count--;
						}
						return true;
					break;
				}
			}
		}
		return false;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide(Vector3::SIDE_DOWN)->getId() === Item::AIR){
				$this->getLevel()->useBreakOn($this);

				return Level::BLOCK_UPDATE_NORMAL;
			}
		}
		return false;
	}

	public function getDrops(Item $item) : array{
		$items = array([MainClass::ITEM_FLOWER_POT, 0, 1]);
		if(($tile = $this->getLevel()->getTile($this)) instanceof FlowerPot){
			if($tile->getFlowerPotItem() !== Item::AIR){
				$items[] = array($tile->getFlowerPotItem(), $tile->getFlowerPotData(), 1);
			}
		}
		return $items;
	}
}
