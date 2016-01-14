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

namespace beito\BugFix;

use pocketmine\plugin\PluginBase;

use pocketmine\inventory\CraftingManager;
use pocketmine\item\Item;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\inventory\ShapelessRecipe;
use pocketmine\Server;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\inventory\CraftItemEvent;

class MainClass extends PluginBase implements Listener {

	const ENABLE_CRAFT_BUG = true;

	const ENABLE_ITEM_DAMAGE_BUG = true;

	const ENABLE_CRAFTING_BUG = true;

	public function onEnable(){
		//CraftBug(RecipeBug)
		if(self::ENABLE_CRAFT_BUG){
			$craftingManager = Server::getInstance()->getCraftingManager();
			$recipes = $craftingManager->getRecipes();
			foreach($recipes as $recipe){
				if($recipe instanceof ShapelessRecipe){
					$map = $this->getRecipeMap($recipe);
					$newRecipe = new ShapedRecipe($recipe->getResult(),
						($map["map"][0] . $map["map"][1] . $map["map"][2]),
						($map["map"][3] . $map["map"][4] . $map["map"][5]),
						($map["map"][6] . $map["map"][7] . $map["map"][8])
					);
					foreach($map["items"] as $key => $item){
						$newRecipe->setIngredient($key, $item);
					}
					$craftingManager->registerRecipe($newRecipe);
				}
			}
		}

		if(self::ENABLE_ITEM_DAMAGE_BUG or self::ENABLE_CRAFTING_BUG){
			Server::getInstance()->getPluginManager()->registerEvents($this, $this);
		}
	}

	public function onBreak(BlockBreakEvent $event){//ItemDamageBug
		$player = $event->getPlayer();
		$item = $event->getItem();
		if($item->isTool() and !$event->isCancelled() and self::ENABLE_ITEM_DAMAGE_BUG){
			if($item->useOn($event->getBlock()) and $item->getDamage() >= $item->getMaxDurability()){
				$player->getInventory()->setItemInHand(Item::get(Item::AIR, 0, 1));
			}else{
				$player->getInventory()->setItemInHand($item);
			}
		}
	}

	public function onCraftItem(CraftItemEvent $event){//CraftingBug
		$recipe = $event->getRecipe();
		if(!$event->isCancelled() and $recipe instanceof ShapedRecipe and self::ENABLE_CRAFTING_BUG){//todo もっと効率的な処理
			$player = $event->getPlayer();

			$event->setCancelled();//イベントをキャンセル

			$mapitems = $recipe->getIngredientMap();
			$items = array();
			foreach($mapitems as $key => $map){//mapから材料となるアイテムをまとめる
				foreach($map as $key2 => $item){
					$r = true;
					foreach($items as $item2){
						if($item->equals($item2)){
							$item2->setCount($item2->getCount() + 1);
							$r = false;
							break;
						}
					}
					if($r and $item->getId() !== Item::AIR){
						$items[] = $item;
					}
				}
			}

			foreach($items as $item){//材料となるアイテムを持っているかを一応確認
				if(!$player->getInventory()->contains($item)){
					echo "test";
					return false;
				}
			}

			$debug = "";
			$contents = $player->getInventory()->getContents();
			foreach($items as $item){//材料アイテムをプレーヤーからとる
				$count = $item->getCount();
				$checkDamage = $item->getDamage() === null ? false : true;
				$checkTags = $item->getCompoundTag() === null ? false : true;
				foreach($contents as $slot => $i){
					if($item->equals($i, $checkDamage, $checkTags)){
						$nc = min($i->getCount(), $count);
						$count -= $nc;
						$newItem = clone $i;
						$newItem->setCount($i->getCount() - $nc);
						$player->getInventory()->setItem($slot, $newItem);
						$debug .= "test:" . $i . "\n";

						$debug .= $newItem . "\ncount." . $count . "\nnc." . $nc . "\nbc." . $item->getCount() . "\n\n";
					}
					if($count <= 0){
						break;
					}
				}
				if($count > 0){
					continue;//...
				}
			}
			$debug .= "result:". $recipe->getResult() . "\n\n";
			$extra = $player->getInventory()->addItem($recipe->getResult());//完成後のアイテムをインベントリへ
			if(count($extra) > 0){
				foreach($extra as $item){//インベントリが一杯だった場合はその場にドロップさせる
					$player->getLevel()->dropItem($player, $item);
				}
			}
			$this->getLogger()->debug($debug);
		}else{
			$event->setCancelled();
		}
	}
	
	public function getRecipeMap(ShapelessRecipe $recipe){//todo 効率的な処理
		$data = array("map" => array(), "items" => array());
		$map = array();
		$mapc = 0;
		$items = $recipe->getIngredientList();
		for($i = 0;$i < count($items);$i++){
			$item = $items[$i];
			
			$map[] = $mapc;
			
			if(isset($items[$i + 1])){
				if($items[$i + 1]->equals($item)){
					continue;
				}
			}
			$r = true;
			foreach($data["items"] as $item2){
				if($item2->equals($item)){
					$r = false;
					break;
				}
			}
			if($r === true){
				$data["items"][$mapc] = $item;
				$mapc++;
			}
		}
		$data["map"] = $map + array(0 => " ", 1 => " ", 2 => " ", 3 => " ", 4 => " ", 5 => " ", 6 => " ", 7 => " ", 8 => " ");
		return $data;
	}
}