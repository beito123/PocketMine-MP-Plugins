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

use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;

class Utils {

	public static function setCustomColorToArmor(Item $item, Color $color){
		if(($hasTag = $item->hasCompoundTag())){
			$tag = $item->getNamedTag();
		}else{
			$tag = new CompoundTag("", array());
		}
		$tag->customColor = new IntTag("customColor", $color->getColorCode());
		$item->setCompoundTag($tag);//
	}

	public static function clearCustomColorToArmor(Item $item){
		if(!$item->hasCompoundTag()) return;
		$tag = $item->getNamedTag();
		if(isset($tag->customColor)){
			unset($tag->customColor);
		}
		$item->setCompoundTag($tag);//
	}

}