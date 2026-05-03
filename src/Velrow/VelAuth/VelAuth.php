<?php

declare(strict_types=1);

namespace Velrow\VelAuth;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Velrow\VelAuth\Manager\AuthManager;
use Velrow\VelAuth\Manager\SessionManager;
use Velrow\VelAuth\Manager\DatabaseManager;
use Velrow\VelAuth\Listener\PlayerListener;
use Velrow\VelAuth\Form\MainMenuForm;

final class VelAuth extends PluginBase {
    use SingletonTrait;

    private AuthManager $authManager;
    private SessionManager $sessionManager;
    private DatabaseManager $databaseManager;

    protected function onLoad(): void {
        self::setInstance($this);
    }

    protected function onEnable(): void {
        $this->saveDefaultConfig();
        
        $this->databaseManager = new DatabaseManager($this);
        $this->sessionManager = new SessionManager();
        $this->authManager = new AuthManager($this->databaseManager, $this->sessionManager);
        
        $this->getServer()->getPluginManager()->registerEvents(
            new PlayerListener($this->authManager, $this->sessionManager),
            $this
        );
        
        $this->getLogger()->info("VelAuth enabled successfully");
    }

    protected function onDisable(): void {
        $this->databaseManager?->close();
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "auth") {
            if (!$sender instanceof Player) {
                $sender->sendMessage("§cЭта команда доступна только в игре!");
                return false;
            }

            if (!$this->sessionManager->isAuthenticated($sender)) {
                $sender->sendMessage("§cВы должны войти в систему!");
                return false;
            }

            MainMenuForm::send($sender);
            return true;
        }

        return false;
    }

    public function getAuthManager(): AuthManager {
        return $this->authManager;
    }

    public function getSessionManager(): SessionManager {
        return $this->sessionManager;
    }

    public function getDatabaseManager(): DatabaseManager {
        return $this->databaseManager;
    }
}
