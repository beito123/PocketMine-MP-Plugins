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

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\Solid;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;
use pocketmine\Server;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\tile\Tile;
use pocketmine\math\Vector3;

use pocketmine\network\protocol\TileEventPacket;

use beito\FlowerPot\MainClass;

class BlockNote extends Solid{

	protected $id = MainClass::BLOCK_NOTE;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function canBeActivated() : bool{
		return true;
	}

	public function getHardness(){
		return 0.8;
	}

	public function getName() : string{
		return "Note Block";
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$this->getLevel()->setBlock($this, $this, true, true);
		$nbt = new CompoundTag("", [
			new StringTag("id", MainClass::TILE_NOTE),
			new IntTag("x", $block->x),
			new IntTag("y", $block->y),
			new IntTag("z", $block->z),
			new ByteTag("note", 0)
		]);
		$pot = Tile::createTile("Note", $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);
		return true;
	}

	public function getInstrument(){
		$downblock = $this->level->getBlock($this->getSide(Vector3::SIDE_DOWN));
		switch($downblock->getId()){//materials...//hmm...
			case Block::WOOD:
			case Block::WOOD2:
			case Block::WOODEN_PLANK:
			case Block::WOODEN_SLABS:
			case Block::DOUBLE_WOOD_SLABS:
			case Block::OAK_WOODEN_STAIRS:
			case Block::SPRUCE_WOODEN_STAIRS:
			case Block::BIRCH_WOODEN_STAIRS:
			case Block::JUNGLE_WOODEN_STAIRS:
			case Block::ACACIA_WOODEN_STAIRS:
			case Block::DARK_OAK_WOODEN_STAIRS:
			case Block::FENCE:
			case Block::FENCE_GATE:
			case Block::FENCE_GATE_SPRUCE:
			case Block::FENCE_GATE_BIRCH:
			case Block::FENCE_GATE_JUNGLE:
			case Block::FENCE_GATE_DARK_OAK:
			case Block::FENCE_GATE_ACACIA:
			case Block::SPRUCE_WOOD_STAIRS:
			case Block::BOOKSHELF:
			case Block::CHEST:
			case Block::CRAFTING_TABLE:
			case Block::SIGN_POST:
			case Block::WALL_SIGN:
			case Block::DOOR_BLOCK:
			case MainClass::BLOCK_NOTE:
				return NoteSound::INSTRUMENT_BASEGUITAR;
			case Block::SAND:
				return NoteSound::INSTRUMENT_SNARE;
			case Block::GLASS:
			case Block::GLASS_PANE:
				return NoteSound::INSTRUMENT_CLICKS;
			case Block::STONE:
			case Block::COBBLESTONE:
			case Block::SANDSTONE:
			case Block::MOSS_STONE:
			case Block::BRICKS:
			case Block::STONE_BRICK:
			case Block::NETHER_BRICKS:
			case Block::QUARTZ_BLOCK:
			case Block::SLAB:
			case Block::COBBLESTONE_STAIRS:
			case Block::BRICK_STAIRS:
			case Block::STONE_BRICK_STAIRS:
			case Block::NETHER_BRICKS_STAIRS:
			case Block::SANDSTONE_STAIRS:
			case Block::QUARTZ_STAIRS:
			case Block::COBBLESTONE_WALL:
			case Block::NETHER_BRICK_FENCE:
			case Block::BEDROCK:
			case Block::GOLD_ORE:
			case Block::IRON_ORE:
			case Block::COAL_ORE:
			case Block::LAPIS_ORE:
			case Block::DIAMOND_ORE:
			case Block::REDSTONE_ORE:
			case Block::GLOWING_REDSTONE_ORE:
			case Block::EMERALD_ORE:
			case Block::FURNACE:
			case Block::BURNING_FURNACE:
			case Block::OBSIDIAN:
			case Block::MONSTER_SPAWNER:
			case Block::NETHERRACK:
			case Block::ENCHANTING_TABLE:
			case Block::END_STONE:
			case Block::STAINED_CLAY:
			case Block::COAL_BLOCK:
				return NoteSound::INSTRUMENT_BASEDRUM;
		}
		return NoteSound::INSTRUMENT_PIANO;
	}

	public function onActivate(Item $item, Player $player = null){
		$tile = $this->level->getTile($this);
		if($tile instanceof Note){
			$instrument = $this->getInstrument();
			$pitch = $tile->getNote() + 1;
			if($pitch < 0 or $pitch > 24){
				$pitch = 0;
			}
			$tile->setNote($pitch);

			$this->level->addSound(new NoteSound($this, $instrument, $pitch));
			return true;
		}
		return false;
	}
}