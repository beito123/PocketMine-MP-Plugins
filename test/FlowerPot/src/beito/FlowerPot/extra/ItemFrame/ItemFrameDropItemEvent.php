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

namespace beito\FlowerPot\extra\ItemFrame;

use pocketmine\block\Block;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\Player;

class ItemFrameDropItemEvent extends BlockEvent implements Cancellable{
	public static $handlerList = null;

	/** @var \pocketmine\Player */
	private $player;
	/** @var \pocketmine\item\Item */
	private $item;
	private $dropChance;

	/**
	 * @param Block    $block
	 * @param Player   $player
	 * @param Item     $dropItem
	 * @param Float    $dropChance
	 */
	public function __construct(Block $block, Player $player, Item $dropItem, $dropChance){
		parent::__construct($block);
		$this->player = $player;
		$this->item = $dropItem;
		$this->dropChance = (float) $dropChance;
	}

	/**
	 * @return Player
	 */
	public function getPlayer(){
		return $this->player;
	}

	/**
	 * @return Item
	 */
	public function getDropItem(){
		return $this->item;
	}

	public function setDropItem(Item $item){
		$this->item = $item;
	}

	/**
	 * @return Float
	 */
	public function getItemDropChance(){
		return $this->dropChance;
	}

	public function setItemDropChance($chance){
		$this->dropChance = (float) $chance;
	}
}