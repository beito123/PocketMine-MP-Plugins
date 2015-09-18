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

/*
 * PocketMine-MP-Bleedingで発生しているクラフトバグを改善します。
 * 
 * MinecaftPE側の問題なのか、PocketMine-MP側の問題なのかはわかりませんが、
 * 不定形レシピが表示されないというサバイバルサーバーでは致命的な問題を改善します。
 * 改善であって根本的かつ完全に修正するわけではありません。
 * 
 * このプラグインは不定形レシピを定形レシピとして登録することで、最低限表示できるようにします。
 * 定形レシピへの変換は自動で行うため、おかしなレシピになる場合があります、ご了承下さい。
 * 
 * (srcを変更できる方は直接CraftingManagerへの変更を行うことをおすすめします。)
 * 
*/

namespace beito\BugFix;

use pocketmine\plugin\PluginBase;

use pocketmine\inventory\CraftingManager;
use pocketmine\item\Item;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\inventory\ShapelessRecipe;
use pocketmine\Server;

class MainClass extends PluginBase {
	
	public function onEnable(){
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
	
	public function getRecipeMap(ShapelessRecipe $recipe){
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