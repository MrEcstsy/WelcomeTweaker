<?php

namespace ecstsy\WelcomeTweaker;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\TextFormat as C;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\StringToItemParser;
use pocketmine\Server;

class WelcomeListener implements Listener {

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $config = Loader::getInstance()->getConfig();

        $messages = $config->getNested("settings.information.messages");
        $items = $config->getNested("settings.join.items");

        if ($config->getNested("settings.information.enabled") === true) {
            foreach ($messages as $message) {
                $player->sendMessage(C::colorize(str_replace(["{PLAYER_NAME}", "{PLAYER_NAMETAG}, {SERVER_ONLINE}", "{SERVER_MAX}", "{SERVER_IP}", "{SERVER_PORT}", "{SERVER_VERSION}", "{PLAYER_PING}"], [$player->getName(), $player->getNameTag(), count(Server::getInstance()->getOnlinePlayers()), Server::getInstance()->getMaxPlayers(), Server::getInstance()->getIp(), Server::getInstance()->getPort(), Server::getInstance()->getVersion(), $player->getNetworkSession()->getPing()], $message)));
            }
        }

        if ($config->getNested("settings.join.enabled") === true) {
            $event->setJoinMessage(C::colorize(str_replace("{PLAYER_NAME}", $player->getName(), $config->getNested("settings.join.message"))));
        }

        if (!$player->hasPlayedBefore()) {
            if (!empty($items)) {
                foreach ($items as $itemData) {
                    $item = StringToItemParser::getInstance()->parse($itemData["item"]);
                    $amount = $itemData["amount"] ?? 1;
                    $item->setCount($amount);
            
                    if (isset($itemData["name"])) {
                        $item->setCustomName(C::colorize($itemData["name"]));
                    }
            
                    if (isset($itemData["lore"]) && is_array($itemData["lore"])) {
                        foreach ($itemData["lore"] as $loreLine) {
                            $item->setLore(array_merge($item->getLore(), [C::colorize($loreLine)]));
                        }
                    }
            
                    if (isset($itemData["enchantments"]) && is_array($itemData["enchantments"])) {
                        foreach ($itemData["enchantments"] as $enchantmentData) {
                            $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentData["enchant"]);
                            $level = $enchantmentData["level"] ?? 1;
                            $item->addEnchantment(new EnchantmentInstance($enchantment, $level));
                        }
                    }
        
                    $player->getInventory()->addItem($item);
                }
            }
        }
    }

    public function onLeave(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        $config = Loader::getInstance()->getConfig();

        if ($config->getNested("settings.leave.enabled") === true) {
            $event->setQuitMessage(C::colorize(str_replace("{PLAYER_NAME}", $player->getName(), $config->getNested("settings.leave.message"))));
        }
    }
}