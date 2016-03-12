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

namespace beito\FlowerPot\extra\Note;

use pocketmine\level\sound\Sound;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\BlockEventPacket;

class NoteSound extends Sound {

	const INSTRUMENT_PIANO = 0;
	const INSTRUMENT_BASEDRUM = 1;
	const INSTRUMENT_SNARE = 2;
	const INSTRUMENT_CLICKS = 3;
	const INSTRUMENT_BASEGUITAR = 4;

	private $instrument = self::INSTRUMENT_PIANO;
	private $pitch = 0;//

	public function __construct(Vector3 $pos, $instrument = self::INSTRUMENT_PIANO, $pitch = 0){
		parent::__construct($pos->x, $pos->y, $pos->z);
		$this->instrument = $instrument;
		$this->pitch = $pitch;
	}

	public function encode(){
		$pk = new BlockEventPacket();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->case1 = $this->instrument;
		$pk->case2 = $this->pitch;
		return $pk;
	}
}