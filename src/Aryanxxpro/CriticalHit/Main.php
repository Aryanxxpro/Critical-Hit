<?php

namespace Aryanxxpro\CriticalHit;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\network\mcpe\protocol\AnimatePacket;

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
                $file = $this->getDataFolder() . "$name.yml";

                if (!file_exists($file)) {
                    return false;
                }

                $config = new Config($file, Config::YAML);

                if (isset($args[0])) {
                    switch ($args[0]) {
                        case "enable":
                            $config->set("critical", true);
                            $config->save();
                            $sender->sendMessage("§aCritical enabled!");
                            break;

                        case "disable":
                            $config->set("critical", false);
                            $config->save();
                            $sender->sendMessage("§cCritical disabled!");
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

    public function onDamage(EntityDamageByEntityEvent $event): void {
        $entity = $event->getEntity();
        $damager = $event->getDamager();

        if ($damager instanceof Player) {
            $name = $damager->getName();
            $file = $this->getDataFolder() . "$name.yml";

            if (!file_exists($file)) return;

            $config = new Config($file, Config::YAML);

            if ($config->get("critical") === true) {
                $packet = new AnimatePacket();
                $packet->action = AnimatePacket::ACTION_CRITICAL_HIT;
                $packet->actorRuntimeId = $entity->getId();
                $damager->getNetworkSession()->sendDataPacket($packet);
            }
        }
    }
}
