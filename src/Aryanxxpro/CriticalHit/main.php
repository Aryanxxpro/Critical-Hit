<?php

namespace Aryanxxpro\CriticalHit;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\network\mcpe\protocol\AnimatePacket;

class main extends PluginBase implements Listener {
    
    public function onEnable(): void{
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onDamage(EntityDamageByEntityEvent $event){
        $entity = $event->getEntity();
        $damager = $event->getDamager();
    
    if ($damager instanceof Player) {
        $packet = new AnimatePacket();
        $packet->action = AnimatePacket::ACTION_CRITICAL_HIT; 
        $packet->actorRuntimeId = $entity->getId();
        $damager->getNetworkSession()->sendDataPacket($packet);
        }
    }
}
