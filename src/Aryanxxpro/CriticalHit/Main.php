<?php

namespace Aryanxxpro\CriticalHit;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\entity\Living;

class Main extends PluginBase implements Listener {

    public function onEnable(): void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $name = $event->getPlayer()->getName();
        $file = $this->getDataFolder() . "$name.yml";

        if (!file_exists($file)) {
            $config = new Config($file, Config::YAML);
            $config->set("critical", true);
            $config->save();
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        $name = $sender->getName();
        $config = new Config($this->getDataFolder() . "$name.yml", Config::YAML);
        
        if ($sender instanceof Player) {
		    switch($command->getName()){
                case "critical":
                    if ($config->get("critical") === true) {
                        $config->set("critical", false);
                        $config->save();
                        $sender->sendMessage("§cCritical hit disabled!");
                    } else {
                        $config->set("critical", true);
                        $config->save();
                        $sender->sendMessage("§aCritical hit enabled!");
                    }
                    return true;
            }
            return true;
        } else {
            $sender->sendMessage("§cUse this command in-game!");
            return false;
        }
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void {
        $damager = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();
        
        if ($damager instanceof Player) {
            $name = $damager->getName();
            $config = new Config($this->getDataFolder() . "$name.yml", Config::YAML);
        
            if ($packet instanceof InventoryTransactionPacket) {
                $invpacket = $packet->trData;
                
                if ($invpacket instanceof UseItemOnEntityTransactionData) {
                    $victim = $damager->getWorld()->getEntity($invpacket->getActorRuntimeId());
                    
                    if ($victim instanceof Living && $config->get("critical") === true) {
                        $critpacket = new AnimatePacket();
                        $critpacket->action = AnimatePacket::ACTION_CRITICAL_HIT;
                        $critpacket->actorRuntimeId = $victim->getId();
                        $damager->getNetworkSession()->sendDataPacket($critpacket);
                    }
                }
            }
        }
    }
}
