<?php

declare(strict_types=1);

namespace Velrow\VelAuth\Manager;

use pocketmine\player\Player;
use Velrow\VelAuth\Utils\PasswordHasher;

final class AuthManager {
    private const MIN_PASSWORD_LENGTH = 6;
    private const MAX_PASSWORD_LENGTH = 32;

    public function __construct(
        private DatabaseManager $database,
        private SessionManager $session
    ) {}

    public function isRegistered(string $username): bool {
        return $this->database->playerExists($username);
    }

    public function register(Player $player, string $password): bool {
        if ($this->isRegistered($player->getName())) {
            return false;
        }

        if (!$this->isPasswordValid($password)) {
            return false;
        }

        $hashedPassword = PasswordHasher::hash($password);
        return $this->database->registerPlayer($player->getName(), $hashedPassword);
    }

    public function login(Player $player, string $password): bool {
        $storedHash = $this->database->getPassword($player->getName());
        
        if ($storedHash === null) {
            return false;
        }

        if (!PasswordHasher::verify($password, $storedHash)) {
            $this->session->addLoginAttempt($player);
            return false;
        }

        $this->session->authenticate($player);
        $this->database->updateLastLogin($player->getName(), $player->getNetworkSession()->getIp());
        
        return true;
    }

    public function changePassword(Player $player, string $oldPassword, string $newPassword): bool {
        if (!$this->isPasswordValid($newPassword)) {
            return false;
        }

        $storedHash = $this->database->getPassword($player->getName());
        
        if ($storedHash === null || !PasswordHasher::verify($oldPassword, $storedHash)) {
            return false;
        }

        $newHash = PasswordHasher::hash($newPassword);
        return $this->database->updatePassword($player->getName(), $newHash);
    }

    public function hasValidSession(Player $player): bool {
        $ip = $player->getNetworkSession()->getIp();
        $session = $this->database->getSession($player->getName(), $ip);
        
        if ($session !== null) {
            $this->session->authenticate($player);
            return true;
        }
        
        return false;
    }

    public function createSession(Player $player, int $duration): void {
        $ip = $player->getNetworkSession()->getIp();
        $this->database->saveSession($player->getName(), $ip, $duration);
    }

    private function isPasswordValid(string $password): bool {
        $length = strlen($password);
        return $length >= self::MIN_PASSWORD_LENGTH && $length <= self::MAX_PASSWORD_LENGTH;
    }
}
