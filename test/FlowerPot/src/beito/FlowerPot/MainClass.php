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

use pocketmine\inventory\CraftingManager;
use pocketmine\inventory\ShapedRecipe;

use pocketmine\Server;

class MainClass extends PluginBase{

	const ITEM_FLOWER_POT = 390;

	const BLOCK_FLOWER_POT = 140;

	public function onEnable(){
		//アイテムの追加
		Item::$list[self::ITEM_FLOWER_POT] = ItemFlowerPot::class;
		//ブロックの追加
		Block::$list[self::BLOCK_FLOWER_POT] = BlockFlowerPot::class;
		$block = new BlockFlowerPot();
		for($data = 0; $data < 16; ++$data){
			Block::$fullList[(self::BLOCK_FLOWER_POT << 4) | $data] = new BlockFlowerPot($data);
		}
		Block::$solid[self::BLOCK_FLOWER_POT] = $block->isSolid();
		Block::$transparent[self::BLOCK_FLOWER_POT] = $block->isTransparent();
		Block::$hardness[self::BLOCK_FLOWER_POT] = $block->getHardness();
		Block::$light[self::BLOCK_FLOWER_POT] = $block->getLightLevel();
		Block::$lightFilter[self::BLOCK_FLOWER_POT] = 1;
		//ブロックタイルエンティティの追加
		Tile::registerTile(FlowerPot::class);
		//アイテムをクリエイティブタブに追加
		Item::addCreativeItem(Item::get(self::ITEM_FLOWER_POT, 0));
		//一応レシピ追加
		Server::getInstance()->getCraftingManager()->registerRecipe((new ShapedRecipe(Item::get(MainClass::ITEM_FLOWER_POT, 0, 1),
			"X X",
			" X "
		))->setIngredient("X", Item::get(Item::BRICK, null)));
	}
}