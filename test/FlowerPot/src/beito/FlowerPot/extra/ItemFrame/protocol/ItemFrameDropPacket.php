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

namespace beito\FlowerPot\extra\ItemFrame\protocol;

use pocketmine\network\protocol\DataPacket;

use beito\FlowerPot\MainClass;

class ItemFrameDropPacket extends DataPacket {

	const NETWORK_ID = MainClass::PROTOCOL_ITEM_FRAME_DROP_ITEM_PACKET;

	public $x;
	public $y;
	public $z;
	public $dropItem;

	public function decode(){
		$this->z = $this->getInt();//hmm...
		$this->y = $this->getInt();
		$this->x = $this->getInt();
		$this->dropItem = $this->getSlot();
		//unknown...
	}

	public function encode(){

	}
}