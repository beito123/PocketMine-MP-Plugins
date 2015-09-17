<?php

namespace beito\otu;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\permission\ServerOperator;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\level\Level;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

class MainClass extends PluginBase implements Listener {
	
	const API = 1.0;
	
	private $config, $messages, $jailMode;
	
	private $players = array(), $jails = array();
	
	public static $defaultConfig = [
		"jails" => array(),
		"otu-jail-select-mode" => "auto",
		"auto-release" => true,
		"auto-respawn-in-jail" => true,
		"show-log-to-console" => true,
	];
	
	public static $messagesConfig = [
		"otued-sender" => "%1さんをotuしました。",
		"otued-player" => "牢屋に転送されました。",
		"otu-release-sender" => "%1を釈放しました。",
		"otu-release-player" => "釈放されました。",
		"otu-not-contacted" => "指定されたプレーヤーは見つかりませんでした",
		"otu-command-can-not-be-used" => "コマンドを使用する事はできません。",
		"otu-you-do-not-have-permission" => "あなたはこのコマンドを使用する権限を持っていません。",
		"runaed-sender" => "%1さんをRunaしました。",
		"runaed-player" => "Runaされました。",
		"runa-release-sender" => "%1のRunaを解除しました。",
		"runa-release-player" => "Runaを解除されました。",
		"runa-not-contacted" => "指定されたプレーヤーは見つかりませんでした",
		"otup-please-parameter" => "パラメーターを入力して下さい。 /otup add <name> [<x> <y> <z> <level>]",
		"otup-level-is-not-loaded" => "指定されたワールドが読み込まれていません。 /otup add <name> [<x> <y> <z> <level>]",
		"otup-coordinates-must-be-number" => "座標は数値で入力して下さい。 /otup add <name> [<x> <y> <z> <level>]",
		"otup-please-jail-name" => "otu牢屋を指定してください。 /otup add <name> [<x> <y> <z> <level>]",
		"otup-jail-success" => "otu牢屋を追加しました。",
		"otup-del-please-jail-name" => "削除したいotu牢屋を指定してください。 /otup del <name>",
		"otup-del-not-exist" => "/otup del <name> : 指定されたotu牢屋が見つかりませんでした。",
		"otup-del-success" => "otu牢屋 %1 の削除に成功しました。",
		"otup-already-exist" => "既に同じ名前の牢屋が存在します。",
		"otup-list-not-number" => "/otup list [ページ番号] : ページ番号は数値で指定して下さい。",
		"otup-list-first-message" => "otu牢屋一覧 (%1/%2) :",
		"otup-list-first-message-console" => "otu牢屋一覧 :",
		"otup-list-message" => "%1 (%2, %3, %4, %5),",
		"otup-help" => "--- コマンド一覧 ---\\n/otup add <牢屋名> [<x> <y> <z> <level>]:otu牢屋を追加します。",
		"otup-help2" => "/otup del <牢屋名>:otu牢屋を削除します。\\n/otup list [ページ番号]:otu牢屋一覧を表示します。",
		"otulist-not-number" => "ページ番号は数値で指定して下さい。",
		"otulist-not-exist-type" => "指定されたタイプのリストは存在しません。",
		"otulist-first-message" => "%1プレーヤー一覧 (%2/%3) :",
		"otulist-first-message-console" => "%1されたプレーヤー一覧 :",
		"otulist-message" => "%1, ",
	];
	
	//中途半端なAPIなため一部API削除(恐らく最低でもEventとか用意しないと用途がない?)
	
	public function getOtuPlayers(){
		return $this->players["otu"];
	}
	
	public function isOtu($name){
		return isset($this->players["otu"][$name]);
	}
	
	public function setOtu($name, $bool){
		if($bool){
			$this->players["otu"][$name] = true;
		}else{
			unset($this->players["otu"][$name]);
		}
	}
	
	public function getRunaPlayers(){
		return $this->players["runa"];
	}
	
	public function isRuna($name){
		return isset($this->players["runa"][$name]);
	}
	
	public function setRuna($name, $bool){
		if($bool){
			$this->players["runa"][$name] = true;
		}else{
			unset($this->players["runa"][$name]);
		}
	}
	
	public function getJailPos($type){
		return (isset($this->jails[$type])) ? $this->jails[$type]:false;
	}
	
	public function existJail($type){
		return (isset($this->jails[$type])) ? $this->jails[$type]:false;
	}
	
	public function addOtuJail($name, Position $pos){
		if(!isset($this->jails[$name])){
			$this->jails[$name] = $pos;
			return true;
		}
		return false;
	}
	
	public function delOtuJail($name){
		if(isset($this->jails[$name])){
			unset($this->jails[$name]);
			return true;
		}
		return false;
	}
	
	public function getJailSelectMode(){
		return $this->settings["jailMode"];
	}
	
	public function getSelectJail($sender, Player $player, $mode){
		switch($mode){
			case "auto":
			case "auto-level":
				if($sender instanceof Player){
					$level = $sender->getLevel();
					$jails = array();
					foreach($this->jails as $pos){
						if($pos->getLevel() === $level){
							$key = $sender->distanceSquared($pos);
							$jails[$key] = $pos;
							//return $pos;
						}
					}
					ksort($jails);
					if(isset($jails[key($jails)])){
						return $jails[key($jails)];
					}
				}
			break;
		}
		if(substr($mode, 0, 5) === "jail-"){
			if(isset($this->jails[substr($this->jailMode, 5)])){
				return $this->jails[substr($this->jailMode, 5)];
			}
		}
		return isset($this->jails[0]) ? $this->jails[0]:new Position(0, 0, 0, Server::getInstance()->getDefaultLevel());
	}
	
	public function isAutoRelease(){
		return $this->settings["autoRelease"];
	}
	
	public function isAutoRespawnInJail(){
		return $this->settings["autoRespawnInJail"];
	}
	
	public function isShowLogToConsole(){
		return $this->settings["showLogToConsole"];
	}
	
	public function onEnable(){
		if(!file_exists($this->getDataFolder())){
			mkdir($this->getDataFolder(), 0755, true);
		}
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, self::$defaultConfig);
		$this->players = (new Config($this->getDataFolder() . "players.yml", Config::YAML, array("otu" => array(), "runa" => array())))->getAll();
		$this->messages = (new Config($this->getDataFolder() . "messages.yml", Config::YAML, self::$messagesConfig))->getAll();
		
		Server::getInstance()->getPluginManager()->registerEvents($this, $this);
		
		$settings = $this->config->getAll();
		
		$this->settings = [
			"jailMode" => $settings["otu-jail-select-mode"],
			"autoRelease" => $settings["auto-release"],
			"autoRespawnInJail" => $settings["auto-respawn-in-jail"],
			"showLogToConsole" => $settings["show-log-to-console"],
		];
		
		$jails = $settings["jails"];
		if(count($jails) > 0){
			foreach($jails as $key => $value){
				$exp = explode(",", $value);
				if(Server::getInstance()->isLevelLoaded($exp[3])){
					$this->jails[$key] = new Position($exp[0], $exp[1], $exp[2], Server::getInstance()->getLevelByName($exp[3]));
				}else{
					$this->jails[$key] = new Position(0, 0, 0, Server::getInstance()->getDefaultLevel());
					$this->getLogger()->notice("otuの牢屋." . $key . " に指定されたワールドが読み込まれていません。 デフォルトのワールドを使用します。");
				}
			}
		}else{
			$this->getLogger()->notice("otuの牢屋が設定されていません。 牢屋の追加は/otup <name> [<x> <y> <z> <level>] で追加できます。");
		}
	}
	
	public function onDisable(){
		$this->save();
	}
	
	//event
	
	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		if($this->isOtu($player->getName()) or $this->isRuna($player->getName())){
			$event->setCancelled();
		}
	}
	
	public function onPlace(BlockBreakEvent $event){
		$player = $event->getPlayer();
		if($this->isOtu($player->getName()) or $this->isRuna($player->getName())){
			$event->setCancelled();
		}
	}
	
	public function onInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		if($this->isOtu($player->getName()) or $this->isRuna($player->getName())){
			$event->setCancelled();
		}
	}
	
	public function onMove(PlayerMoveEvent $event){
		$player = $event->getPlayer();
		if($this->isRuna($player->getName())){
			$event->setCancelled();
		}
	}
	
	public function onCommandPreprocess(PlayerCommandPreprocessEvent $event){
		$player = $event->getPlayer();
		if($this->isOtu($player->getName()) or $this->isRuna($player->getName())){
			if($event->getMessage()[0] === "/"){
				$command = explode(" ", $event->getMessage())[0];
				if($command === "/otu" or $command === "/runa"){
					if(!$player->hasPermission("otu.allow.otucommand")){
						$this->sendCustomMessage($player, "otu-you-do-not-have-permission", array($command));
						$event->setCancelled();
					}
				}elseif($command !== "/register" and $command !== "/login"){
					$this->sendCustomMessage($player, "otu-command-can-not-be-used", array($command));
					$event->setCancelled();
				}
			}
		}
	}
	
	public function onEntityDamageByEntity(EntityDamageEvent $event){
		if($event instanceof EntityDamageByEntityEvent){
			$damager = $event->getDamager();
			if($damager instanceof Player){
				if($this->isOtu($damager->getName()) or $this->isRuna($damager->getName())){
					$this->setCancelled();
				}
			}
		}
	}
	
	public function onRespawn(PlayerRespawnEvent $event){//途中...
		$player = $event->getPlayer();
		if($this->isOtu($player->getName()) or $this->isRuna($player->getName())){
			if($this->isAutoRespawnInJail()){
				$event->setRespawnPosition($event->getRespawnPosition());
			}
		}
	}
	
	public function onKick(PlayerKickEvent $event){
		$player = $event->getPlayer();
		if($player->isBanned()){
			if(($this->isOtu($player->getName()) or $this->isRuna($player->getName())) and $this->isAutoRelease()){
				$this->setOtu($player->getName(), false);
				$this->setRuna($player->getName(), false);
				$type = ($this->isOtu($player->getName())) ? "otu":"runa";
				if($this->isShowLogToConsole()){
					$this->getLogger()->info("自動釈放により" . $player->getName() . "さんの" . $type . "を解除しました");
				}
			}
		}
	}
	
	//command
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		switch(strtolower($command->getName())){
			case "otu":
				if(!isset($args[0])) return false;
				$player = Server::getInstance()->getPlayer($args[0]);
				if($player instanceof Player){
					$name = $player->getName();
				}else{
					$name = $args[0];
				}
				if(!$this->isOtu($name)){
					$this->setOtu($name, true);
					$this->sendCustomMessage($sender, "otued-sender", array($name));
					if($player instanceof Player){
						$player->teleport($this->getSelectJail($sender, $player, $this->getJailSelectMode()));
						$this->sendCustomMessage($player, "otued-player", array($sender->getName()));
					}
					if(!($sender instanceof ConsoleCommandSender) and $this->isShowLogToConsole()){
						$this->getLogger()->info($sender->getName() . "さんが" . $name . "さんにotuをしました");
					}
				}else{
					if($player instanceof Player){
						if($sender instanceof Player){
							$player->teleport($sender);
						}else{
							$player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
						}
						$this->sendCustomMessage($player, "otu-release-player", array("%1" => $sender->getName()));
					}
					$this->setOtu($name, false);
					$this->sendCustomMessage($sender, "otu-release-sender", array("%1" => $name));
					if(!($sender instanceof ConsoleCommandSender) and $this->isShowLogToConsole()){
						$this->getLogger()->info($sender->getName() . "さんが" . $name . "さんのotuを解除しました");
					}
				}
				return true;
			break;
			case "runa":
				if(!isset($args[0])) return false;
				$player = Server::getInstance()->getPlayer($args[0]);
				if($player instanceof Player){
					$name = $player->getName();
				}else{
					$name = $args[0];
				}
				if(!$this->isRuna($name)){
					$this->setRuna($name, true);
					$this->sendCustomMessage($sender, "runaed-sender", array($name));
					if($player instanceof Player){
						$this->sendCustomMessage($player, "runaed-player", array($sender->getName()));
					}
					if(!($sender instanceof ConsoleCommandSender) and $this->isShowLogToConsole()){
						$this->getLogger()->info($sender->getName() . "さんが" . $name . "さんにrunaをしました");
					}
				}else{
					$this->setRuna($name, false);
					$this->sendCustomMessage($sender, "runa-release-sender", array($name));
					if($player instanceof Player){
						$this->sendCustomMessage($player, "runa-release-player", array($sender->getName()));
					}
					if(!($sender instanceof ConsoleCommandSender) and $this->isShowLogToConsole()){
						$this->getLogger()->info($sender->getName() . "さんが" . $name . "さんのrunaを解除しました");
					}
				}
				return true;
			break;
			case "otulist":
				$type = (isset($args[0])) ? $args[0]:"otu";
				$page = (isset($args[1])) ? $args[1]:0;
				if(!is_numeric($page)){
					$this->sendCustomMessage("otulist-not-number", array($page));
					return true;
				}
				if($type === "otu"){
					$list = $this->getOtuPlayers();
				}elseif($type === "runa"){
					$list = $this->getRunaPlayers();
				}else{
					$this->sendCustomMessage($sender, "otulist-not-exist-type");
					return true;
				}
				$page = max(0, min($page, floor(count($list) / 4) - 1));
				if(!($sender instanceof ConsoleCommandSender)){
					$l = array_chunk($list, 4, true);
					$list = (isset($l[$page])) ? $l[$page]:array();
				}
				
				if(!($sender instanceof ConsoleCommandSender)){//floor(count($this->jails) / 4) + 1)
					$message = $this->getCustomMessage("otulist-first-message", array($type, $page + 1, floor(count($list) / 4) + 1)) . "\n";
				}else{
					$message = $this->getCustomMessage("otulist-first-message-console", array($type, $page + 1, floor(count($list) / 4) + 1)) . "\n";
				}
				foreach($list as $key => $value){
					$message .= $this->getCustomMessage("otulist-message", array($key));
				}
				$sender->sendMessage($message);
				return true;
			break;
			case "otup":
				if(!isset($args[0])) $args[0] = "help";
				switch($args[0]){
					case "add":
						if(!isset($args[1])){
							$this->sendCustomMessage($sender, "otup-please-jail-name");
							return true;
						}
						if(count($args) >= 3  and !isset($args[5]) and $sender instanceof ConsoleCommandSender){
							$this->sendCustomMessage($sender, "otup-please-parameter");
							return true;
						}
						
						$name = $args[1];
						if(isset($args[5])){
							if(Server::getInstance()->isLevelLoaded($args[5])){
								$x = $args[2];
								$y = $args[3];
								$z = $args[4];
								$level = Server::getInstance()->getLevelByName($args[5]);
							}else{
								$this->sendCustomMessage($sender, "otup-level-is-not-loaded");
								return true;
							}
						}else{
							$x = (int) $sender->x;
							$y = (int) $sender->y;
							$z = (int) $sender->z;
							$level = $sender->getLevel();
						}
						
						if(is_numeric($x) and is_numeric($y) and is_numeric($z)){
							if($name !== null and $this->existJail($name)){
								$this->sendCustomMessage($sender, "otup-already-exist");
								return true;
							}
							$this->addOtuJail($name, new Position($x, $y, $z, $level));
							$this->sendCustomMessage($sender, "otup-jail-success");
							if(!($sender instanceof ConsoleCommandSender) and $this->isShowLogToConsole()){
								$this->getLogger()->info($sender->getName() . "さんがotu牢屋を追加しました");
							}
						}else{
							$this->sendCustomMessage($sender, "otup-coordinates-must-be-number");
						}
					break;
					case "del":
						if(!isset($args[1])){
							$this->sendCustomMessage($sender, "otup-del-please-jail-name");
							return true;
						}
						if($this->existJail($args[1])){
							$this->delOtuJail($args[1]);
							$this->sendCustomMessage($sender, "otup-del-success", array($args[1]));
							if(!($sender instanceof ConsoleCommandSender) and $this->isShowLogToConsole()){
								$this->getLogger()->info($sender->getName() . "さんがotu牢屋 " . $args[1] . "を削除しました");
							}
						}else{
							$this->sendCustomMessage($sender, "otup-del-not-exist");
						}
					break;
					case "list":
						$page = (isset($args[1])) ? ($args[1] - 1):0;
						if(!is_numeric($page)){
							$this->sendCustomMessage("otup-list-not-number", array($page));
							return true;
						}
						$page = max(0, min($page, floor(count($this->jails) / 4)));
						if(!($sender instanceof ConsoleCommandSender)){
							$j = array_chunk($this->jails, 4, true);
							$jails = isset($j[$page]) ? $j[$page]:array();
						}else{
							$jails = $this->jails;
						}
						if(!($sender instanceof ConsoleCommandSender)){
							$message = $this->getCustomMessage("otup-list-first-message", array($page + 1, floor(count($this->jails) / 4) + 1)) . "\n";
						}else{
							$message = $this->getCustomMessage("otup-list-first-message-console", array($page + 1, floor(count($this->jails) / 4) + 1)) . "\n";
						}
						foreach($jails as $key => $value){
							$message .= $this->getCustomMessage("otup-list-message", array($key, $value->getX(), $value->getY(), $value->getZ(), $value->getLevel()->getName())) . "\n";
						}
						$sender->sendMessage($message);
					break;
					default:
						$this->sendCustomMessage($sender, "otup-help", array(), false);
						$this->sendCustomMessage($sender, "otup-help2", array(), false);
				}
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
	
	public function save(){
		$players = new Config($this->getDataFolder() . "players.yml", Config::YAML);
		$players->setAll($this->players);
		$players->save();
		$jailsdata = array();
		foreach($this->jails as $key => $value){
			if($value instanceof Position){
				$jailsdata[$key] = $value->x . "," . $value->y . "," . $value->z . "," . $value->getLevel()->getName();
			}
		}
		$this->config->set("jails", $jailsdata);
		$this->config->save();
	}
}