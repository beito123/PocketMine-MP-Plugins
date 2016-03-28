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

use pocketmine\block\Air;//todo: optimize import...
use pocketmine\block\Block;
use pocketmine\block\Solid;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\item\Potion;
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
			new ListTag("Items", [])
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

	public function update(){//umm... right update method...?
		$this->getLevel()->setBlock($this, Block::get($this->id, $this->meta + 1), true);
		$this->getLevel()->setBlock($this, $this, true);//Undo the damage value
	}

	public function isEmpty(){
		return $this->meta === 0x00;
	}

	public function isFull(){
		return $this->meta === 0x06;
	}

	public function onActivate(Item $item, Player $player = null){//long...
		$tile = $this->getLevel()->getTile($this);
		if(!($tile instanceof Cauldron)){
			return false;
		}

		switch($item->getId()){
			case Item::BUCKET:
				if($item->getDamage() === 0){//empty bucket
					if(!$this->isFull() or $tile->isCustomColor() or $tile->hasPotion()){
						break;
					}
					$bucket = clone $item;
					$bucket->setDamage(8);//water bucket
					Server::getInstance()->getPluginManager()->callEvent($ev = new PlayerBucketFillEvent($player, $this, 0, $item, $bucket));
					if(!$ev->isCancelled()){
						if($player->isSurvival()){
							$player->getInventory()->setItemInHand($ev->getItem(), $player);
						}
						$this->meta = 0;//empty
						$this->getLevel()->setBlock($this, $this, true);
						$tile->clearCustomColor();
						$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
					}
				}elseif($item->getDamage() === 8){//water bucket
					if($this->isFull() and !$tile->isCustomColor() and !$tile->hasPotion()){
						break;
					}
					$bucket = clone $item;
					$bucket->setDamage(0);//empty bucket
					Server::getInstance()->getPluginManager()->callEvent($ev = new PlayerBucketEmptyEvent($player, $this, 0, $item, $bucket));
					if(!$ev->isCancelled()){
						if($player->isSurvival()){
							$player->getInventory()->setItemInHand($ev->getItem(), $player);
						}

						if($tile->hasPotion()){//if has potion
							$this->meta = 0;//empty
							$this->getLevel()->setBlock($this, $this, true);
							$tile->setPotionId(0xffff);//reset potion
							$tile->clearCustomColor();
							$this->getLevel()->addSound(new ExplodeSound($this->add(0.5, 0, 0.5)));
						}else{
							$this->meta = 6;//fill
							$this->getLevel()->setBlock($this, $this, true);
							$tile->clearCustomColor();
							$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
						}
						$this->update();
					}
				}
				break;
			case Item::DYE:
				if($tile->hasPotion()) break;
				$color = Color::getDyeColor($item->getDamage());
				if($tile->isCustomColor()){
					$color = Color::averageColor($color, $tile->getCustomColor());
				}
				if($player->isSurvival()){
					$item->setCount($item->getCount() - 1);
					/*if($item->getCount() <= 0){
						$player->getInventory()->setItemInHand(Item::get(Item::AIR));
					}*/
				}
				$tile->setCustomColor($color);
				$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
				
				$this->update();
				break;
			case Item::LEATHER_CAP:
			case Item::LEATHER_TUNIC:
			case Item::LEATHER_PANTS:
			case Item::LEATHER_BOOTS:
				if($this->isEmpty()) break;
				if($tile->isCustomColor()){
					--$this->meta;
					$this->getLevel()->setBlock($this, $this, true);

					$newItem = clone $item;
					Utils::setCustomColorToArmor($newItem, $tile->getCustomColor());
					$player->getInventory()->setItemInHand($newItem);

					$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));

					if($this->isEmpty()){
						$tile->clearCustomColor();
					}
				}else{
					--$this->meta;
					$this->getLevel()->setBlock($this, $this, true);

					$newItem = clone $item;
					Utils::clearCustomColorToArmor($newItem);
					$player->getInventory()->setItemInHand($newItem);

					$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
				}
				break;
			case Item::POTION:
			case Item::SPLASH_POTION:
				if(!$this->isEmpty() and (($tile->getPotionId() !== $item->getDamage() and $item->getDamage() !== Potion::WATER_BOTTLE) or 
					($item->getId() === Item::POTION and $tile->getSplashPotion()) or 
						($item->getId() === Item::SPLASH_POTION and !$tile->getSplashPotion()) or 
							($item->getDamage() === Potion::WATER_BOTTLE and $tile->hasPotion()))){//long...
					$this->meta = 0x00;
					$this->getLevel()->setBlock($this, $this, true);
					$tile->setPotionId(0xffff);//reset
					$tile->setSplashPotion(false);

					if($player->isSurvival()){
						$player->getInventory()->setItemInHand(Item::get(Item::GLASS_BOTTLE));
					}
					$this->getLevel()->addSound(new ExplodeSound($this->add(0.5, 0, 0.5)));
				}elseif($item->getDamage() === Potion::WATER_BOTTLE){//water bottle
					$this->meta += 2;
					if($this->meta > 0x06) $this->meta = 0x06;
					$this->getLevel()->setBlock($this, $this, true);

					if($player->isSurvival()){
						$player->getInventory()->setItemInHand(Item::get(Item::GLASS_BOTTLE));
					}

					$tile->setPotionId(0xffff);
					$tile->setSplashPotion(false);
					$tile->clearCustomColor();
					$this->getLevel()->addSound(new SplashSound($this->add(0.5, 1, 0.5)));
				}elseif(!$this->isFull()){
					$this->meta += 2;
					if($this->meta > 0x06) $this->meta = 0x06;
					$this->getLevel()->setBlock($this, $this, true);
					$tile->setPotionId($item->getDamage());
					$tile->setSplashPotion($item->getId() === Item::SPLASH_POTION);
					$tile->clearCustomColor();

					if($player->isSurvival()){
						$player->getInventory()->setItemInHand(Item::get(Item::GLASS_BOTTLE));
					}
					$color = Potion::getColor($item->getDamage());
					$this->getLevel()->addSound(new SpellSound($this->add(0.5, 1, 0.5), $color[0], $color[1], $color[2]));
				}
				break;
			case Item::GLASS_BOTTLE:
				if($this->meta < 2) break;
				if($tile->hasPotion()){
					$this->meta -= 2;
					if($this->meta < 0x00) $this->meta = 0x00;
					$this->getLevel()->setBlock($this, $this, true);

					if($player->isSurvival()){
						$result = Item::get(Item::POTION, $tile->getPotionId());
						if($item->getCount() === 1){
							$player->getInventory()->setItemInHand($result);
						}else{
							$player->getInventory()->addItem($result);
						}
					}

					if($this->isEmpty()){
						$tile->setPotionId(0xffff);//reset
						$tile->setSplashPotion(false);
					}
					$color = Potion::getColor($result->getDamage());
					$this->getLevel()->addSound(new SpellSound($this->add(0.5, 1, 0.5), $color[0], $color[1], $color[2]));
				}else{
					$this->meta -= 2;
					if($this->meta < 0x00) $this->meta = 0x00;
					$this->getLevel()->setBlock($this, $this, true);

					if($player->isSurvival()){
						$result = Item::get(Item::POTION, Potion::WATER_BOTTLE);
						if($item->getCount() > 1 and $player->getInventory()->canAddItem($result)){
							$player->getInventory()->addItem($result);
						}else{
							$player->getInventory()->setItemInHand($result);
						}
					}

					$this->getLevel()->addSound(new GraySplashSound($this->add(0.5, 1, 0.5)));
				}
				break;
		}
		return true;
	}
}