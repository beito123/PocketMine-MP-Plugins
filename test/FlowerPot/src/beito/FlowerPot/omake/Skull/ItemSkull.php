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

namespace beito\FlowerPot\omake\Skull;

use pocketmine\block\Block;
use pocketmine\item\Item;

use beito\FlowerPot\MainClass;

class ItemSkull extends Item{

	const SKELETON_SKULL = 0;
	const WITHER_SKELETON_SKULL = 1;
	const ZOMBIE_HEAD = 2;
	const HEAD = 3;
	const CREEPER_HEAD = 4;

	public static $names = [
		self::SKELETON_SKULL => "Skeleton Skull",
		self::WITHER_SKELETON_SKULL => "Wither Skeleton Skull",
		self::ZOMBIE_HEAD => "Zombie Head",
		self::HEAD => "Head",
		self::CREEPER_HEAD => "Creeper Head",
	];

	public function __construct($meta = 0, $count = 1){
		$this->block = Block::get(MainClass::BLOCK_SKULL);
		$name = (isset(self::$names[$meta])) ? self::$names[$meta]:"Mob Head";
		parent::__construct(MainClass::ITEM_SKULL, $meta, $count, $name);
	}
}