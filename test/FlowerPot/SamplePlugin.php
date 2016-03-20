<?php
/**
 * SamplePlugin
 * @name SamplePlugin
 * @main beito\SamplePlugin\MainClass
 * @version 1.0.0
 * @api 2.0.0
 * @author beito
 */
namespace beito\SamplePlugin{
	use pocketmine\plugin\PluginBase;
	use pocketmine\Server;
	use pocketmine\Player;

	use pocketmine\event\block\BlockBreakEvent;
	use pocketmine\event\player\PlayerInteractEvent;

	use beito\FlowerPot\MainClass as FlowerPotMain;
	use beito\FlowerPot\extra\ItemFrame\ItemFrameDropItemEvent;

	class MainClass extends PluginBase{
		public function onEnable(){
			Server::getInstance()->getPluginManager()->registerEvents(new EventListener(), $this);
		}
	}

	class EventListener implements \pocketmine\event\Listener{

		public function onItemFrameDrop(ItemFrameDropItemEvent $event){
			$player = $event->getPlayer();
			if(!$player->isOp()){
				$player->sendMessage("You do not have permission.");
				$event->setCancelled();
			}
		}

		//extra//ついで
		
		public function onBreak(BlockBreakEvent $event){
			$player = $event->getPlayer();
			if($event->getBlock()->getId() === FlowerPotMain::BLOCK_ITEM_FRAME and !$player->isOp()){
				$player->sendMessage("You do not have permission.");
				$event->setCancelled();
			}
		}
		
		public function onInteract(PlayerInteractEvent $event){
			$player = $event->getPlayer();
			if($event->getBlock()->getId() === FlowerPotMain::BLOCK_ITEM_FRAME and !$player->isOp()){
				$player->sendMessage("You do not have permission.");
				$event->setCancelled();
			}
		}
	}
}