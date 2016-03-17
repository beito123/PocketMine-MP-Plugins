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

use pocketmine\level\sound\GenericSound;
use pocketmine\math\Vector3;

use beito\FlowerPot\MainClass;

class GraySplashSound extends GenericSound {

	public function __construct(Vector3 $pos, $pitch = 0){
		parent::__construct($pos, MainCLass::EVENT_SOUND_GRAY_SPLASH, $pitch);
	}
}