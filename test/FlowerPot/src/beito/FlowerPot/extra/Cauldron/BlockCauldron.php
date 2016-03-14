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

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\Solid;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;
use pocketmine\Server;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\tile\Tile;
use pocketmine\math\Vector3;

use beito\FlowerPot\MainClass;

class BlockCauldron extends Solid {

	protected $id = MainClass::BLOCK_CAULDRON;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getHardness(){
		return 2;
	}

	public function getName() : string{
		return "Cauldron";
	}

	public function canBeActivated() : bool{
		return true;
	}

	public function getBoundingBox(){//todo fix(?)
		return new AxisAlignedBB(
			$this->x,
			$this->y,
			$this->z,
			$this->x + 1,
			$this->y + 1,
			$this->z + 1
		);
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}


	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$nbt = new CompoundTag("", [
			new StringTag("id", MainClass::TILE_CAULDRON),
			new IntTag("x", $block->x),
			new IntTag("y", $block->y),
			new IntTag("z", $block->z),
			new ShortTag("PotionId", 0xffff),
			new ByteTag("SplashPotion", 0),
			new ListTag("Items", []),
			new IntTag("CustomColor", 0x00ffffff)//todo: set default value...(unknown...)//use the padding as a flag...
		]);
		$chunk = $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4);
		$tile = Tile::createTile("Cauldron", $chunk, $nbt);//

		$this->getLevel()->setBlock($block, $this, true, true);
		return true;
	}

	public function onBreak(Item $item){
		$this->getLevel()->setBlock($this, new Air(), true, true, true);
		return true;
	}

	public function getDrops(Item $item) : array{
		if($item->isPickaxe() >= 1){
			return [
				[MainClass::ITEM_CAULDRON, 0, 1]
			];
		}
		return [];
	}

	public function onActivate(Item $item, Player $player = null){
		$tile = $this->getLevel()->getTile($this);
		if($tile instanceof Cauldron){
			if($tile->getPotionId() === 0xff){
				if($item->getId() === Item::POTION or $item->getId() === Item::SPLASH_POTION){
					//todo
				}elseif($item->getId() === Item::BUCKET and $this->getDamage() === 8){//water bucket//todo explosion

				}
			}else{
				if($tile->getCustomColorPadding() === 0x00){//water
					if($item->getId() === Item::BUCKET){//bucket
						switch($item->getDamage()){//todo: call the PlayerBucket(Empty|Fill)Event
							case 0://empty
								if($this->meta === 0x06){//if fill
									$this->meta = 0x00;//empty
									$bucket = clone $item;
									$bucket->setDamage(8);//water bucket
									$this->getLevel()->setBlock($this, $this, true);
									if($player->isSurvival()){
										$player->getInventory()->setItemInHand($bucket, $player);
									}
									$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
								}
								break;
							case 8://water bucket
								if($this->meta === 0x00){//if empty
									$this->meta = 0x06;//fill
									$bucket = clone $item;
									$bucket->setDamage(0);//empty bucket
									$this->getLevel()->setBlock($this, $this, true);
									if($player->isSurvival()){
										$player->getInventory()->setItemInHand($bucket, $player);
									}
									$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
								}
								break;
						}
					}elseif($item->getId() === Item::DYE and $this->meta > 0){//set dye color
						$color = Color::getDyeColor($item->getDamage());
						if($color != null){
							$tile->setCustomColor($color);
							$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
						}
					}
				}else{//colored water
					if($item->getId() === Item::DYE){
						$color = Color::getDyeColor($item->getDamage());
						if($color != null){
							$dyedColor = Color::averageColor($color, $tile->getCustomColor());
							//echo "test: " . $dyedColor;//debug
							$tile->setCustomColor($dyedColor);
							$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
						}
					}elseif($item->getId() >= Item::LEATHER_CAP and $item->getId() <= Item::LEATHER_BOOTS){
						//todo: dyeing
					}elseif($item->getId() === Item::BUCKET and $item->getId() === 0){//empty bucket
						//todo: reset
					}
				}
			}
		}
		
		return true;
	}
}