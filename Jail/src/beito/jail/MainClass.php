<?php

namespace beito\jail;

use pocketmine\block\Block;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\level\Position;

class MainClass extends PluginBase implements Listener {
	
	const API = 1.1;
	
	private $jails = array(), $messages = array(), $logs = array();
	
	private static $messagesConfig = [
		"jail-success" => "%1さんを牢屋に入れました!",
		"jail-type-not-exist" => "指定された牢屋が存在しません。",
		"jail-not-contacted" => "%1さんは接続していません。",
		"unjail-success" => "牢屋を撤去しました。",
		"unjail-not-placed" => "牢屋が設置されていません。",
		"unjailall-success" => "%1個の牢屋を撤去しました。",
	];
	
	public static $defaultJailBlocks = [
		array(0, -1, 0, Block::BEDROCK, 0),
		array(0, -1, -1, Block::BEDROCK, 0),
		array(0, -1, 1, Block::BEDROCK, 0),
		array(-1, -1, 0, Block::BEDROCK, 0),
		array(1, -1, 0, Block::BEDROCK, 0),
		array(-1, -1, -1, Block::BEDROCK, 0),
		array(1, -1, -1, Block::BEDROCK, 0),
		array(-1, -1, 1, Block::BEDROCK, 0),
		array(1, -1, 1, Block::BEDROCK, 0),
		array(0, 0, 0, Block::TORCH, 0),//
		array(0, 0,-1, Block::BEDROCK, 0),
		array(0, 0,1, Block::BEDROCK, 0),
		array(-1, 0, 0, Block::BEDROCK, 0),
		array(1, 0, 0, Block::BEDROCK, 0),
		array(-1, 0,-1, Block::BEDROCK, 0),
		array(1, 0,-1, Block::BEDROCK, 0),
		array(-1, 0,1, Block::BEDROCK, 0),
		array(1, 0,1, Block::BEDROCK, 0),
		array(0, 1, 0, Block::AIR, 0),//
		array(0, 1, -1, Block::IRON_BAR, 0),
		array(0, 1, 1, Block::IRON_BAR, 0),
		array(-1, 1, 0, Block::IRON_BAR, 0),
		array(1, 1, 0, Block::IRON_BAR, 0),
		array(-1, 1, -1, Block::BEDROCK, 0),
		array(1, 1, -1, Block::BEDROCK, 0),
		array(-1, 1, 1, Block::BEDROCK, 0),
		array(1, 1, 1, Block::BEDROCK, 0),
		array(0, 2, 0, Block::BEDROCK, 0),//
		array(0, 2, -1, Block::BEDROCK, 0),
		array(0, 2, 1, Block::BEDROCK, 0),
		array(-1, 2, 0, Block::BEDROCK, 0),
		array(1, 2, 0, Block::BEDROCK, 0),
		array(-1, 2, -1, Block::BEDROCK, 0),
		array(1, 2, -1, Block::BEDROCK, 0),
		array(-1, 2, 1, Block::BEDROCK, 0),
		array(1, 2, 1, Block::BEDROCK, 0),
	];
	
	//
	
	public function getJails(){
		return $jails;
	}
	
	public function addJail($name, $blocks, $aliases = array()){
		$this->jails[$name] = array("blocks" => ((is_array($blocks)) ? $blocks:array()), "aliases" => $aliases);
	}
	
	public function addJailClass(BaseJail $jail){
		if(!isset($this->jails[$jail->getName()])){
			$this->jails[$jail->getName()] = $jail;
			return true;
		}
		return false;
	}
	
	public function existJail($name){
		if(isset($this->jails[$name])){
			return true;
		}
		return false;
	}
	
	public function getJail($name){
		foreach($this->jails as $jail){
			if($jail instanceof BaseJail){
				$aliases = $jail->getAliases();
			}else{
				$aliases = (isset($jail["aliases"])) ? $jail["aliases"]:array();
			}
			foreach($aliases as $alias){
				if($alias === $name){
					if($jail instanceof BaseJail){
						return $jail->getJailBlocks();
					}else{
						return new BaseJail(key($jail), $jail["blocks"], $jail["aliases"]);
					}
				}
			}
		}
		return null;
	}
	
	public function getJailBlocks($name){
		foreach($this->jails as $jail){
			if($jail instanceof BaseJail){
				$aliases = $jail->getAliases();
			}else{
				$aliases = (isset($jail["aliases"])) ? $jail["aliases"]:array();
			}
			foreach($aliases as $alias){
				if($alias === $name){
					if($jail instanceof BaseJail){
						return $jail->getJailBlocks($alias);
					}else{
						return $jail["blocks"];
					}
				}
			}
		}
		return false;
	}
	
	public function placeJail(Position $pos, $type = "default", $key = null){
		$blocks = $this->getJailBlocks($type);
		if($blocks !== false){
			$x = (int) $pos->getX();
			$y = (int) $pos->getY();
			$z = (int) $pos->getZ();
			$level = $pos->getLevel();
			$log = array($level);
			foreach($blocks as $data){//data[0] x座標, data[1] y座標, data[2] z座標, data[3] blockid, data[4] meta値
				$pos = new Vector3($x + $data[0], $y + $data[1], $z + $data[2]);
				$block = Block::get($data[3], $data[4]);
				if($key !== null){
					$log[] = array($pos, $level->getBlock($pos));
				}
				$level->setBlock($pos, $block);
			}
			if($key !== null){
				$this->logs[$key][] = $log;
			}
			return true;
		}
		return false;
	}
	
	public function rollbackJail($key){
		if(isset($this->logs[$key])){
			if(count($this->logs[$key]) > 0){
				$blocks = array_pop($this->logs[$key]);
				$level = array_shift($blocks);
				foreach($blocks as $block){
					$level->setBlock($block[0], $block[1]);
				}
				unset($this->logs[$key][key($blocks)]);
				return true;
			}else{
				unset($this->logs[$key]);
			}
		}
		return false;
	}
	
	public function allRollbackJail(){
		$c = 0;
		foreach($this->logs as $key => $log){
			foreach($log as $blocks){
				if($this->rollbackJail($key)){
					++$c;
				}
			}
		}
		return $c;
	}
	
	//
	
	public function onEnable(){
		if(!file_exists($this->getDataFolder())){
			mkdir($this->getDataFolder(), 0755, true);
		}
		$this->messages = (new Config($this->getDataFolder() . "messages.yml", Config::YAML, self::$messagesConfig))->getAll();
		
		$this->addJail("Default", self::$defaultJailBlocks, array("default", "DEFAULT", "0"));//デフォルトの牢屋の追加
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		switch(strtolower($command->getName())){
			case "jail":
				if(!isset($args[0])) return false;
				$player = Server::getInstance()->getPlayer($args[0]);
				if($player instanceof Player){
					$type = (isset($args[1])) ? $args[1]:"default";
					if($this->placeJail($player, $type, $sender->getName())){
						$player->teleport(new Position((int) $player->x + 0.5, (int) $player->y, (int) $player->z + 0.5));
						$this->sendCustomMessage($sender, "jail-success", array($player->getName()));
					}else{
						$this->sendCustomMessage($sender, "jail-type-not-exist", array($type));
					}
				}else{
					$this->sendCustomMessage($sender, "jail-not-contacted", array($args[0]));
				}
				return true;
			break;
			case "unjail":
				if($this->rollbackJail($sender->getName())){
					$this->sendCustomMessage($sender, "unjail-success", array());
				}else{
					$this->sendCustomMessage($sender, "unjail-not-placed", array());
				}
				return true;
			break;
			case "unjailall":
				$c = $this->allRollbackJail();
				$this->sendCustomMessage($sender, "unjailall-success", array($c));
				return true;
			break;
		}
		return false;
	}
	
	public function getCustomMessage($key, $args = array()){
		$message = (isset($this->messages[$key])) ? $this->messages[$key]:"";
		if($message !== ""){//何も記載していない場合は表示されないように(非表示機能)
			$i = 1;
			foreach($args as $value){
				$message = str_replace("%" . $i, $value, $message);
				++$i;
			}
			return str_replace("\\n", "\n", $message);
		}
		return null;
	}
	
	public function sendCustomMessage($player, $key, $args = array(), $pname = true){
		$message = $this->getCustomMessage($key, $args);
		if($message !== null){
			$player->sendMessage((($pname) ? "[otu] ":"") . $message);
		}
		return false;
	}
	
}