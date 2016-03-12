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

use pocketmine\plugin\PluginBase;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\tile\Tile;
use pocketmine\entity\Entity;

use pocketmine\inventory\CraftingManager;
use pocketmine\inventory\ShapedRecipe;

use pocketmine\Server;

use beito\FlowerPot\extra\Skull\Skull;
use beito\FlowerPot\extra\Skull\BlockSkull;
use beito\FlowerPot\extra\Skull\ItemSkull;

use beito\FlowerPot\extra\Note\Note;
use beito\FlowerPot\extra\Note\BlockNote;

use beito\FlowerPot\extra\BrewingStand\BrewingStand;
use beito\FlowerPot\extra\BrewingStand\BlockBrewingStand;
use beito\FlowerPot\extra\BrewingStand\ItemBrewingStand;

use beito\FlowerPot\extra\ItemFrame\block\ItemFrame as BlockItemFrame;
use beito\FlowerPot\extra\ItemFrame\item\ItemFrame as ItemItemFrame;
use beito\FlowerPot\extra\ItemFrame\protocol\ItemFrameDropPacket;
use beito\FlowerPot\extra\ItemFrame\tile\ItemFrame;

use beito\FlowerPot\extra\Cauldron\Cauldron;
use beito\FlowerPot\extra\Cauldron\BlockCauldron;
use beito\FlowerPot\extra\Cauldron\ItemCauldron;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\math\Vector3;

class MainClass extends PluginBase implements Listener {

	//

	const ITEM_FLOWER_POT = 390;

	const BLOCK_FLOWER_POT = 140;

	//

	const ITEM_SKULL = 397;

	const BLOCK_SKULL = 144;

	//

	const BLOCK_NOTE = 25;

	const TILE_NOTE = "Music";

	//

	const ITEM_ITEM_FRAME = 389;

	const TILE_ITEM_FRAME = "ItemFrame";

	const BLOCK_ITEM_FRAME = 199;

	const PROTOCOL_ITEM_FRAME_DROP_ITEM_PACKET = 0xca;

	//
	
	const TILE_CAULDRON = "Cauldron";
	
	const ITEM_CAULDRON = 380;

	const BLOCK_CAULDRON = 118;

	const EVENT_SOUND_SPLASH = 3506;//umm..

	public function onEnable(){
		//flower pot
		//add item
		Item::$list[self::ITEM_FLOWER_POT] = ItemFlowerPot::class;
		//add block
		$this->registerBlock(self::BLOCK_FLOWER_POT, BlockFlowerPot::class);
		//add block entity(tile)
		Tile::registerTile(FlowerPot::class);
		//add to creative item
		$this->addCreativeItem(Item::get(self::ITEM_FLOWER_POT, 0));

		//extra: skull
		
		//add item
		Item::$list[self::ITEM_SKULL] = ItemSkull::class;
		//add block
		$this->registerBlock(self::BLOCK_SKULL, BlockSkull::class);
		//add block entity(tile)
		Tile::registerTile(Skull::class);
		//add to creative item
		$this->addCreativeItem(Item::get(self::ITEM_SKULL, 0));
		$this->addCreativeItem(Item::get(self::ITEM_SKULL, 1));
		$this->addCreativeItem(Item::get(self::ITEM_SKULL, 2));
		$this->addCreativeItem(Item::get(self::ITEM_SKULL, 3));
		$this->addCreativeItem(Item::get(self::ITEM_SKULL, 4));

		//extra: note block
		
		//add item(block)
		Item::$list[self::BLOCK_NOTE] = BlockNote::class;
		//add block
		$this->registerBlock(self::BLOCK_NOTE, BlockNote::class);
		//add block entity(tile)
		Tile::registerTile(Note::class);
		//add creative item
		$this->addCreativeItem(Item::get(self::BLOCK_NOTE, 0));

		//extra: item frame
		
		//add block
		$this->registerBlock(self::BLOCK_ITEM_FRAME, BlockItemFrame::class);
		//add item
		Item::$list[self::ITEM_ITEM_FRAME] = ItemItemFrame::class;
		//add block entity(tile)
		Tile::registerTile(ItemFrame::class);
		//add to creative item
		$this->addCreativeItem(Item::get(self::ITEM_ITEM_FRAME, 0));
		//add drop packet to network
		Server::getInstance()->getNetWork()->registerPacket(MainClass::PROTOCOL_ITEM_FRAME_DROP_ITEM_PACKET, ItemFrameDropPacket::class);

		//extra: Cauldron
		
		//add item
		Item::$list[self::ITEM_CAULDRON] = ItemCauldron::class;
		//add block
		$this->registerBlock(self::BLOCK_CAULDRON, BlockCauldron::class);
		//add block entity(tile)
		Tile::registerTile(Cauldron::class);
		//add creative item
		$this->addCreativeItem(Item::get(self::ITEM_CAULDRON, 0));
		
		Server::getInstance()->getPluginManager()->registerEvents($this, $this);
	}

	public function onPacketReceive(DataPacketReceiveEvent $event){
		$packet = $event->getPacket();
		if($packet::NETWORK_ID === MainClass::PROTOCOL_ITEM_FRAME_DROP_ITEM_PACKET){
			//var_dump($packet);//debug
			
			$player = $event->getPlayer();

			$level = $player->getLevel();
			$tile = $level->getTile(new Vector3($packet->x, $packet->y, $packet->z));
			if($tile instanceof ItemFrame){
				if($tile->getItem()->getId() !== Item::AIR){
					if((mt_rand(0, 10) / 10) <= $tile->getItemDropChance()){//
						$level->dropItem($tile, $tile->getItem());//todo motion and modify the coordinates
					}
					$tile->setItem(Item::get(Item::AIR));
					$tile->setItemRotation(0);
				}
			}
		}
	}

	public function registerBlock($id, $class){
		Block::$list[$id] = $class;
		$block = new $class();
		for($data = 0; $data < 16; ++$data){
			Block::$fullList[($id << 4) | $data] = new $class($data);
		}
		Block::$solid[$id] = $block->isSolid();
		Block::$transparent[$id] = $block->isTransparent();
		Block::$hardness[$id] = $block->getHardness();
		Block::$light[$id] = $block->getLightLevel();
		Block::$lightFilter[$id] = 1;//
	}

	public function addCreativeItem(Item $item){
		Item::addCreativeItem($item);
	}
}