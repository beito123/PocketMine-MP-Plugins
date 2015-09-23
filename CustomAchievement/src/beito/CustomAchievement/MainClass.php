<?php

namespace beito\CustomAchievement;

use pocketmine\Achievement;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class MainClass extends PluginBase{

	private $achievements;

	public static $defaultAchievements = [
		/*"openInventory" => array(
			"name" => "所持品の確認",
			"requires" => [],
		),*/
		"mineWood" => [
			"name" => "木を手に入れる",
			"requires" => [ //"openInventory",
			],
		],
		"buildWorkBench" => [
			"name" => "土台作り",
			"requires" => [
				"mineWood",
			],
		],
		"buildPickaxe" => [
			"name" => "いざ採掘 !",
			"requires" => [
				"buildWorkBench",
			],
		],
		"buildFurnace" => [
			"name" => "ホットトピック",
			"requires" => [
				"buildPickaxe",
			],
		],
		"acquireIron" => [
			"name" => "金属を手に入れる",
			"requires" => [
				"buildFurnace",
			],
		],
		"buildHoe" => [
			"name" => "いざ農業 !",
			"requires" => [
				"buildWorkBench",
			],
		],
		"makeBread" => [
			"name" => "パンを焼こう",
			"requires" => [
				"buildHoe",
			],
		],
		"bakeCake" => [
			"name" => "The cake is a lie",
			"requires" => [
				"buildHoe",
			],
		],
		"buildBetterPickaxe" => [
			"name" => "アップグレード",
			"requires" => [
				"buildPickaxe",
			],
		],
		"buildSword" => [
			"name" => "いざ突撃 !",
			"requires" => [
				"buildWorkBench",
			],
		],
		"diamonds" => [
			"name" => "ダイヤモンド !",
			"requires" => [
				"acquireIron",
			],
		],
	];

	public function onEnable(){
		$this->achievements = new Config($this->getDataFolder() . "achievements.yml", Config::YAML, array(
			"achievements" => self::$defaultAchievements,
		));
		Achievement::$list = $this->achievements;
	}
}