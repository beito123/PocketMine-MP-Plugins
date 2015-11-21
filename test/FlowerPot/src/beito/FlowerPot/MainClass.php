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

namespace beito\FlowerPot;

use pocketmine\plugin\PluginBase;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\tile\Tile;
use pocketmine\entity\Entity;

use pocketmine\inventory\CraftingManager;
use pocketmine\inventory\ShapedRecipe;

use pocketmine\Server;

use beito\FlowerPot\omake\Skull\Skull;
use beito\FlowerPot\omake\Skull\BlockSkull;
use beito\FlowerPot\omake\Skull\ItemSkull;

use beito\FlowerPot\omake\Note\Note;
use beito\FlowerPot\omake\Note\BlockNote;

class MainClass extends PluginBase{

	const ITEM_FLOWER_POT = 390;

	const BLOCK_FLOWER_POT = 140;

	const ITEM_SKULL = 397;

	const BLOCK_SKULL = 144;

	const BLOCK_NOTE = 25;

	const TILE_NOTE = "Music";

	public function onEnable(){
		//アイテムの追加
		Item::$list[self::ITEM_FLOWER_POT] = ItemFlowerPot::class;
		//ブロックの追加
		$this->registerBlock(self::BLOCK_FLOWER_POT, BlockFlowerPot::class);
		//ブロックタイルエンティティの追加
		Tile::registerTile(FlowerPot::class);
		//アイテムをクリエイティブタブに追加
		Item::addCreativeItem(Item::get(self::ITEM_FLOWER_POT, 0));
		//一応レシピ追加
		Server::getInstance()->getCraftingManager()->registerRecipe((new ShapedRecipe(Item::get(MainClass::ITEM_FLOWER_POT, 0, 1),
			"X X",
			" X "
		))->setIngredient("X", Item::get(Item::BRICK, null)));

		//omake skull
		
		//アイテムの追加
		Item::$list[self::ITEM_SKULL] = ItemSkull::class;
		//ブロックの追加
		$this->registerBlock(self::BLOCK_SKULL, BlockSkull::class);
		//ブロックタイルエンティティの追加
		Tile::registerTile(Skull::class);
		//アイテムをクリエイティブタブに追加
		Item::addCreativeItem(Item::get(self::ITEM_SKULL, 0));
		Item::addCreativeItem(Item::get(self::ITEM_SKULL, 1));
		Item::addCreativeItem(Item::get(self::ITEM_SKULL, 2));
		Item::addCreativeItem(Item::get(self::ITEM_SKULL, 3));
		Item::addCreativeItem(Item::get(self::ITEM_SKULL, 4));

		//omake note block
		
		$this->registerBlock(self::BLOCK_NOTE, BlockNote::class);
		Tile::registerTile(Note::class);
		Item::addCreativeItem(Item::get(self::BLOCK_NOTE, 0));
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
		Block::$lightFilter[$id] = 1;
	}
}