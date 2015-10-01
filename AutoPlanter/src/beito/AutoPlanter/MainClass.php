<?php

namespace beito\AutoPlanter;

use pocketmine\block\Block;
use pocketmine\block\Crops;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\math\Vector3;

class MainClass extends PluginBase implements Listener {

	private $blocks = array(), $task = -1;

	public function onEnable(){	
		Server::getInstance()->getPluginManager()->registerEvents($this, $this);
	}

	public function onBreak(BlockBreakEvent $event){
		$block = $event->getBlock();
		if($block instanceof Crops and !$event->getItem()->isHoe()){
			//$this->blocks[] = $block;
			$this->blocks[microtime(true) + mt_rand(1, 5) . "." . mt_rand(0, 5)] = $block;
			$this->runTask();
		}
	}

	public function runBlockPlace(){
		echo "test";
		foreach($this->blocks as $key => $block){
			/*if(mt_rand(0, 2) === 0){
				if($block->getLevel()->getBlock($block->getSide(Vector3::SIDE_DOWN))->getId() === Block::FARMLAND){
					$block->getLevel()->setBlock($block, Block::get($block->getId(), 0));
				}
				unset($this->blocks[$key]);
			}*/
			if($key <= microtime(true)){
				if($block->getLevel()->getBlock($block->getSide(Vector3::SIDE_DOWN))->getId() === Block::FARMLAND){
					$block->getLevel()->setBlock($block, Block::get($block->getId(), 0));
				}
				unset($this->blocks[$key]);
			}
		}
		if(!(count($this->blocks) > 0)){
			$this->cancelTask();
		}
	}

	public function runTask(){
		if(Server::getInstance()->getScheduler()->isQueued($this->task)){
			return false;
		}
		$this->task = Server::getInstance()->getScheduler()->scheduleDelayedRepeatingTask(new BlockPlaceTask($this), 20, 20)->getTaskId();
		return true;
	}

	public function cancelTask(){
		if(Server::getInstance()->getScheduler()->isQueued($this->task)){
			Server::getInstance()->getScheduler()->cancelTask($this->task);
		}
	}
}

class BlockPlaceTask extends PluginTask {

	public function __construct(Plugin $owner){
		$this->owner = $owner;
	}

	public function onRun($tick){
		if($this->owner instanceof MainClass){
			$this->owner->runBlockPlace();
		}
	}
}