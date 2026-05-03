<?php

declare(strict_types=1);

namespace Velrow\VelAuth\Manager;

use pocketmine\player\Player;

final class SessionManager {
    private array $authenticatedPlayers = [];
    private array $loginAttempts = [];

    public function isAuthenticated(Player $player): bool {
        return isset($this->authenticatedPlayers[$player->getName()]);
    }

    public function authenticate(Player $player): void {
        $this->authenticatedPlayers[$player->getName()] = true;
        $this->resetLoginAttempts($player);
    }

    public function deauthenticate(Player $player): void {
        unset($this->authenticatedPlayers[$player->getName()]);
    }

    public function addLoginAttempt(Player $player): void {
        $name = $player->getName();
        if (!isset($this->loginAttempts[$name])) {
            $this->loginAttempts[$name] = 0;
        }
        $this->loginAttempts[$name]++;
    }

    public function getLoginAttempts(Player $player): int {
        return $this->loginAttempts[$player->getName()] ?? 0;
    }

    public function resetLoginAttempts(Player $player): void {
        unset($this->loginAttempts[$player->getName()]);
    }

    public function isBlocked(Player $player, int $maxAttempts): bool {
        return $this->getLoginAttempts($player) >= $maxAttempts;
    }
}
