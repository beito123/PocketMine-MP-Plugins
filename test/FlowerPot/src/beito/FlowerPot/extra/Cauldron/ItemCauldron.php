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

use pocketmine\block\Block;
use pocketmine\item\Item;

use beito\FlowerPot\MainClass;

class ItemCauldron extends Item{

	public function __construct($meta = 0, $count = 1){
		$this->block = Block::get(MainClass::BLOCK_CAULDRON);
		parent::__construct(MainClass::ITEM_CAULDRON, $meta, $count, "Cauldron");
	}
}