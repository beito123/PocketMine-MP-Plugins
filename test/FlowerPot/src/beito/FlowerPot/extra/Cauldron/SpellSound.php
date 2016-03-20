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

use pocketmine\level\sound\Sound;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\LevelEventPacket;

use beito\FlowerPot\MainClass;

class SpellSound extends Sound {

	private $id;
	private $color;

	public function __construct(Vector3 $pos, $r = 0, $g = 0, $b = 0){
		parent::__construct($pos->x, $pos->y, $pos->z);
		$this->id = (int) MainClass::EVENT_SOUND_SPELL;
		$this->color = ($r << 16 | $g << 8 | $b) & 0xffffff;
	}

	public function encode(){
		$pk = new LevelEventPacket;
		$pk->evid = $this->id;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->data = $this->color;
		
		return $pk;
	}
}