<?php
/**
 * Created by PhpStorm.
 * User: Dylan Taylor
 * Date: 27/04/2017
 * Time: 19:07
 */

namespace DevTools\commands;


use DevTools\DevTools;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class GeneratePluginCommand extends DevToolsCommand{

	public function __construct(DevTools $plugin){
		parent::__construct("genplugin", $plugin);
		$this->setUsage("/genplugin <pluginName> <authorName>");
		$this->setDescription("Generates skeleton files for a plugin");
		$this->setPermission("devtools.command.genplugin");
	}

	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$this->getPlugin()->isEnabled()){
			return false;
		}

		if(!$this->testPermission($sender)){
			return false;
		}

		if(count($args) < 2){
			$sender->sendMessage(TextFormat::RED . "Usage: " . $this->usageMessage);
			return true;
		}

		list($pluginName, $author) = $args;
		$directory = $this->getPlugin()->getServer()->getPluginPath() . DIRECTORY_SEPARATOR . $pluginName;

		if($this->getPlugin()->getServer()->getPluginManager()->getPlugin($pluginName) !== null or file_exists($directory)){
			$sender->sendMessage(TextFormat::RED . "A plugin with this name already exists on the server. Please choose a different name or remove the other plugin.");
			return true;
		}

		mkdir($directory . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . $author . DIRECTORY_SEPARATOR . $pluginName, 0755, true);

		if(preg_match("/[^A-Za-z0-9_-]/", $pluginName) !== 0 or preg_match("/[^A-Za-z0-9_-]/", $author) !== 0){
			$sender->sendMessage(TextFormat::RED . "Plugin name and author name must contain only letters, numbers, underscores and dashes.");
		}

		$namespace = $author . "\\" . $pluginName;

		$this->getPlugin()->saveResource("plugin_skeleton/plugin.yml", true);
		$this->getPlugin()->saveResource("plugin_skeleton/Main.php", true);

		$pluginYml = file_get_contents($this->getPlugin()->getDataFolder() . DIRECTORY_SEPARATOR . "plugin_skeleton" . DIRECTORY_SEPARATOR . "plugin.yml");
		$pluginYml = str_replace("%{PluginName}", $pluginName, $pluginYml);
		$pluginYml = str_replace("%{ApiVersion}", $this->getPlugin()->getServer()->getApiVersion(), $pluginYml);
		$pluginYml = str_replace("%{AuthorName}", $author, $pluginYml);
		$pluginYml = str_replace("%{Namespace}", $namespace, $pluginYml);
		file_put_contents($directory . DIRECTORY_SEPARATOR . "plugin.yml", $pluginYml);

		$mainClass = file_get_contents($this->getPlugin()->getDataFolder() . DIRECTORY_SEPARATOR . "plugin_skeleton" . DIRECTORY_SEPARATOR . "Main.php");
		$mainClass = str_replace("#%{Namespace}", "namespace " . $namespace . ";", $mainClass);
		file_put_contents($directory . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . $author . DIRECTORY_SEPARATOR . $pluginName .  DIRECTORY_SEPARATOR . "Main.php", $mainClass);

		$sender->sendMessage("Created skeleton plugin $pluginName in " . $directory);
		return true;
	}
}