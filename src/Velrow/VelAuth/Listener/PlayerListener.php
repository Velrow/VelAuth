<?php

declare(strict_types=1);

namespace Velrow\VelAuth\Listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\player\Player;
use Velrow\VelAuth\Manager\AuthManager;
use Velrow\VelAuth\Manager\SessionManager;
use Velrow\VelAuth\Form\LoginForm;
use Velrow\VelAuth\Form\RegisterForm;
use Velrow\VelAuth\VelAuth;

final class PlayerListener implements Listener {
    public function __construct(
        private AuthManager $authManager,
        private SessionManager $sessionManager
    ) {}

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        
        if ($this->authManager->hasValidSession($player)) {
            $player->sendMessage("§aАвтоматический вход выполнен!");
            return;
        }

        $this->showAuthForm($player);
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $this->sessionManager->deauthenticate($event->getPlayer());
    }

    public function onMove(PlayerMoveEvent $event): void {
        if (!$this->sessionManager->isAuthenticated($event->getPlayer())) {
            $event->cancel();
        }
    }

    public function onInteract(PlayerInteractEvent $event): void {
        if (!$this->sessionManager->isAuthenticated($event->getPlayer())) {
            $event->cancel();
        }
    }

    public function onDropItem(PlayerDropItemEvent $event): void {
        if (!$this->sessionManager->isAuthenticated($event->getPlayer())) {
            $event->cancel();
        }
    }

    public function onItemUse(PlayerItemUseEvent $event): void {
        if (!$this->sessionManager->isAuthenticated($event->getPlayer())) {
            $event->cancel();
        }
    }

    public function onChat(PlayerChatEvent $event): void {
        if (!$this->sessionManager->isAuthenticated($event->getPlayer())) {
            $event->cancel();
            $event->getPlayer()->sendMessage("§cВы должны войти в систему!");
        }
    }

    public function onDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        if (!$entity instanceof Player) {
            return;
        }

        if (!$this->sessionManager->isAuthenticated($entity)) {
            $event->cancel();
        }

        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            if ($damager instanceof Player && !$this->sessionManager->isAuthenticated($damager)) {
                $event->cancel();
            }
        }
    }

    public function onBlockBreak(BlockBreakEvent $event): void {
        if (!$this->sessionManager->isAuthenticated($event->getPlayer())) {
            $event->cancel();
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event): void {
        if (!$this->sessionManager->isAuthenticated($event->getPlayer())) {
            $event->cancel();
        }
    }

    public function onInventoryOpen(InventoryOpenEvent $event): void {
        $player = $event->getPlayer();
        if (!$this->sessionManager->isAuthenticated($player)) {
            $event->cancel();
        }
    }

    public function onInventoryTransaction(InventoryTransactionEvent $event): void {
        $player = $event->getTransaction()->getSource();
        if ($player instanceof Player && !$this->sessionManager->isAuthenticated($player)) {
            $event->cancel();
        }
    }

    public function onCommand(CommandEvent $event): void {
        $sender = $event->getSender();
        if ($sender instanceof Player && !$this->sessionManager->isAuthenticated($sender)) {
            $event->cancel();
            $sender->sendMessage("§cВы должны войти в систему!");
        }
    }

    private function showAuthForm(Player $player): void {
        $isRegistered = $this->authManager->isRegistered($player->getName());
        
        if ($isRegistered) {
            LoginForm::send($player);
        } else {
            RegisterForm::send($player);
        }
    }
}
