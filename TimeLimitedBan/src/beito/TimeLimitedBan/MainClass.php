<?php

/*
 * Copyright (c) 2015 beito
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
*/

namespace beito\TimeLimitedBan;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\permission\BanEntry;
use pocketmine\Server;
use pocketmine\Player;

class MainClass extends PluginBase {

	public function onEnable(){
		
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		switch(strtolower($command->getName())){
			case "tban":
				if(!isset($args[1])) return false;
				$name = array_shift($args);
				$time = array_shift($args);
				$dateTime = $this->getDateTime($time);
				$reason = implode(" ", $args);
				Server::getInstance()->getNameBans()->addBan($name, $reason, $dateTime, $sender->getName());
				
				if(($player = $sender->getServer()->getPlayerExact($name)) instanceof Player){
					$player->kick($reason !== "" ? "Banned by admin for " . $time . " minutes Reason: " . $reason : "", true);
				}
				$sender->sendMessage("[TBan] " . $name . "さんを" . ($time) . "分間Banしました。");
				return true;
			break;
			case "tban-ip":
				if(!isset($args[1])) return false;
				$value = array_shift($args);
				$time = array_shift($args);
				$dateTime = $this->getDateTime($time);
				$reason = implode(" ", $args);

				$ip = $value;
				$isIp = false;
				if(!preg_match("/^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$/", $value)){
					if(($player = Server::getInstance()->getPlayer($value)) instanceof Player){
						$ip = $player->getAddress();
					}else{
						$sender->sendMessage("[TBan] " . $value . "さんはサーバーに接続していません。");
						return true;
					}
				}else{
					$isIp = true;
				}
				
				Server::getInstance()->getIPBans()->addBan($ip, $reason, $dateTime, $sender->getName());

				foreach(Server::getInstance()->getOnlinePlayers() as $player){
					if($player->getAddress() === $ip){
						$player->kick($reason !== "" ? $reason : "IP Banned for " . $time . " minutes.", true);
					}
				}

				Server::getInstance()->getNetwork()->blockAddress($ip, $time * 60);
				if($isIp){
					$sender->sendMessage("[TBan] IPアドレス " . $value . " を" . $time . "分間IPBanしました。");
				}else{
					$sender->sendMessage("[TBan] " . $value . "さんを" . $time . "分間IPBanしました。");
				}
				return true;
			break;
		}
		return false;
	}

	public function getDateTime($minute){
		$t = new \DateTime("@" . (time() + ($minute * 60)));
		$t->setTimezone(new \DateTimeZone(date_default_timezone_get()));
		$t->format(BanEntry::$format);
		return $t;
	}
}