<?php

namespace ecstsy\WelcomeTweaker;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Loader extends PluginBase {

    use SingletonTrait;

    public function onLoad(): void
    {
        self::setInstance($this);
    }

    public function onEnable(): void
    {
        $currentVersion = $this->getConfig()->get("version");

        if ($currentVersion === null || $currentVersion !== "1.0") {
            $this->getLogger()->info($currentVersion === null ? "Updating configuration to new format" : "Updating configuration to version 1.0");
            $this->saveOldConfig();
            $this->saveDefaultConfig();
        }
        $this->getServer()->getPluginManager()->registerEvents(new WelcomeListener(), $this);
    }

    private function saveOldConfig(): void
    {
        $oldConfigPath = $this->getDataFolder() . "old_config.yml";
        $this->saveResource("config.yml", false);
        rename($this->getDataFolder() . "config.yml", $oldConfigPath);
    }
}