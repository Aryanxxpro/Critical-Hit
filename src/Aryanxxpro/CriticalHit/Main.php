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

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool {
        if ($cmd->getName() === "critical") {
            if ($sender instanceof Player) {
                $name = $sender->getName();
                $config = new Config($this->getDataFolder() . "$name.yml", Config::YAML);

                if (isset($args[0])) {
                    switch ($args[0]) {
                        case "enable":
                            $config->set("critical", true);
                            $config->save();
                            $sender->sendMessage("§aCritical hit enabled!");
                            break;

                        case "disable":
                            $config->set("critical", false);
                            $config->save();
                            $sender->sendMessage("§cCritical hit disabled!");
                            break;

                        default:
                            $sender->sendMessage("§cUsage: §7/critical [§aenable §7/ §cdisable§7]");
                            break;
                    }
                } else {
                    $sender->sendMessage("§cUsage: §7/critical [§aenable §7/ §cdisable§7]");
                }
                return true;
            } else {
                $sender->sendMessage("§cUse this command in-game!");
                return false;
            }
        }
        return false;
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
