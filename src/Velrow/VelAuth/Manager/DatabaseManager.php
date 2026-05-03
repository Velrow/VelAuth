<?php

declare(strict_types=1);

namespace Velrow\VelAuth\Manager;

use pocketmine\plugin\Plugin;
use SQLite3;

final class DatabaseManager {
    private SQLite3 $database;

    public function __construct(Plugin $plugin) {
        $dataFolder = $plugin->getDataFolder();
        if (!is_dir($dataFolder)) {
            mkdir($dataFolder);
        }

        $this->database = new SQLite3($dataFolder . "players.db");
        $this->initialize();
    }

    private function initialize(): void {
        $this->database->exec("
            CREATE TABLE IF NOT EXISTS players (
                username TEXT PRIMARY KEY,
                password TEXT NOT NULL,
                email TEXT,
                last_ip TEXT,
                last_login INTEGER,
                registered_at INTEGER NOT NULL,
                pin_code TEXT,
                two_factor_enabled INTEGER DEFAULT 0,
                auto_login_enabled INTEGER DEFAULT 1
            )
        ");

        $this->database->exec("
            CREATE TABLE IF NOT EXISTS sessions (
                username TEXT PRIMARY KEY,
                ip TEXT NOT NULL,
                expires_at INTEGER NOT NULL
            )
        ");
    }

    public function playerExists(string $username): bool {
        $stmt = $this->database->prepare("SELECT 1 FROM players WHERE LOWER(username) = LOWER(:username)");
        $stmt->bindValue(":username", $username, SQLITE3_TEXT);
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_NUM) !== false;
    }

    public function registerPlayer(string $username, string $hashedPassword): bool {
        $stmt = $this->database->prepare("
            INSERT INTO players (username, password, registered_at) 
            VALUES (:username, :password, :time)
        ");
        $stmt->bindValue(":username", $username, SQLITE3_TEXT);
        $stmt->bindValue(":password", $hashedPassword, SQLITE3_TEXT);
        $stmt->bindValue(":time", time(), SQLITE3_INTEGER);
        return $stmt->execute() !== false;
    }

    public function getPassword(string $username): ?string {
        $stmt = $this->database->prepare("SELECT password FROM players WHERE LOWER(username) = LOWER(:username)");
        $stmt->bindValue(":username", $username, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        return $result["password"] ?? null;
    }

    public function updatePassword(string $username, string $hashedPassword): bool {
        $stmt = $this->database->prepare("UPDATE players SET password = :password WHERE LOWER(username) = LOWER(:username)");
        $stmt->bindValue(":password", $hashedPassword, SQLITE3_TEXT);
        $stmt->bindValue(":username", $username, SQLITE3_TEXT);
        return $stmt->execute() !== false;
    }

    public function updateLastLogin(string $username, string $ip): void {
        $stmt = $this->database->prepare("
            UPDATE players 
            SET last_ip = :ip, last_login = :time 
            WHERE LOWER(username) = LOWER(:username)
        ");
        $stmt->bindValue(":ip", $ip, SQLITE3_TEXT);
        $stmt->bindValue(":time", time(), SQLITE3_INTEGER);
        $stmt->bindValue(":username", $username, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function getPlayersByIp(string $ip): array {
        $stmt = $this->database->prepare("SELECT username FROM players WHERE last_ip = :ip");
        $stmt->bindValue(":ip", $ip, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        $players = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $players[] = $row["username"];
        }
        
        return $players;
    }

    public function getPlayerIp(string $username): ?string {
        $stmt = $this->database->prepare("SELECT last_ip FROM players WHERE LOWER(username) = LOWER(:username)");
        $stmt->bindValue(":username", $username, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        return $result["last_ip"] ?? null;
    }

    public function getPlayerData(string $username): ?array {
        $stmt = $this->database->prepare("
            SELECT username, last_ip, last_login, registered_at 
            FROM players 
            WHERE LOWER(username) = LOWER(:username)
        ");
        $stmt->bindValue(":username", $username, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        return $result ?: null;
    }

    public function getAllPlayers(): array {
        $result = $this->database->query("SELECT username FROM players ORDER BY username ASC");
        
        $players = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $players[] = $row["username"];
        }
        
        return $players;
    }

    public function isAutoLoginEnabled(string $username): bool {
        $stmt = $this->database->prepare("
            SELECT auto_login_enabled 
            FROM players 
            WHERE LOWER(username) = LOWER(:username)
        ");
        $stmt->bindValue(":username", $username, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        return ($result["auto_login_enabled"] ?? 1) === 1;
    }

    public function setAutoLogin(string $username, bool $enabled): void {
        $stmt = $this->database->prepare("
            UPDATE players 
            SET auto_login_enabled = :enabled 
            WHERE LOWER(username) = LOWER(:username)
        ");
        $stmt->bindValue(":enabled", $enabled ? 1 : 0, SQLITE3_INTEGER);
        $stmt->bindValue(":username", $username, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function saveSession(string $username, string $ip, int $duration): void {
        $stmt = $this->database->prepare("
            INSERT OR REPLACE INTO sessions (username, ip, expires_at) 
            VALUES (:username, :ip, :expires)
        ");
        $stmt->bindValue(":username", $username, SQLITE3_TEXT);
        $stmt->bindValue(":ip", $ip, SQLITE3_TEXT);
        $stmt->bindValue(":expires", time() + $duration, SQLITE3_INTEGER);
        $stmt->execute();
    }

    public function getSession(string $username, string $ip): ?int {
        $stmt = $this->database->prepare("
            SELECT expires_at FROM sessions 
            WHERE LOWER(username) = LOWER(:username) AND ip = :ip
        ");
        $stmt->bindValue(":username", $username, SQLITE3_TEXT);
        $stmt->bindValue(":ip", $ip, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        if ($result === false) {
            return null;
        }

        if ($result["expires_at"] < time()) {
            $this->deleteSession($username);
            return null;
        }

        return $result["expires_at"];
    }

    public function deleteSession(string $username): void {
        $stmt = $this->database->prepare("DELETE FROM sessions WHERE LOWER(username) = LOWER(:username)");
        $stmt->bindValue(":username", $username, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function close(): void {
        $this->database->close();
    }
}
