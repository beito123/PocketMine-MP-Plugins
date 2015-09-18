<?php

namespace beito\jail;

use pocketmine\plugin\Plugin;

class BaseJail{//いるかを検討...
	
	private $plugin, $name, $blocks = array(), $aliases = array();
	
	public function __construct($name, $blocks, $aliases = array(), Plugin $plugin = null){
		$this->name = $name;
		$this->blocks = $blocks;
		$this->aliases = $aliases;
		$this->plugin = $plugin;
	}
	
	public function getPlugin(){
		return $this->plugin;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function getAliases(){
		return $this->aliases;
	}
	
	public function getJailBlocks($alias){
		return $this->blocks;
	}
}