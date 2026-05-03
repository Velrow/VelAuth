<?php

declare(strict_types=1);

namespace Velrow\VelAuth\Form;

use pocketmine\player\Player;
use jojoe77777\FormAPI\CustomForm;
use Velrow\VelAuth\VelAuth;

final class LoginForm {
    public static function send(Player $player): void {
        $sessionManager = VelAuth::getInstance()->getSessionManager();
        $maxAttempts = VelAuth::getInstance()->getConfig()->get("max-login-attempts", 3);

        if ($sessionManager->isBlocked($player, $maxAttempts)) {
            $player->kick("§cПревышено количество попыток входа!", false);
            return;
        }

        $form = new CustomForm(function (Player $player, ?array $data): void {
            if ($data === null) {
                self::send($player);
                return;
            }

            $password = $data[1] ?? "";

            if (empty($password)) {
                $player->sendMessage("§cВведите пароль!");
                self::send($player);
                return;
            }

            $authManager = VelAuth::getInstance()->getAuthManager();
            
            if ($authManager->login($player, $password)) {
                $authManager->createSession($player, 86400 * 7);
                $player->sendMessage("§aВход выполнен успешно!");
            } else {
                $sessionManager = VelAuth::getInstance()->getSessionManager();
                $attempts = $sessionManager->getLoginAttempts($player);
                $maxAttempts = VelAuth::getInstance()->getConfig()->get("max-login-attempts", 3);
                $remaining = $maxAttempts - $attempts;
                
                if ($remaining > 0) {
                    $player->sendMessage("§cНеверный пароль! Осталось попыток: §e{$remaining}");
                    self::send($player);
                } else {
                    $player->kick("§cПревышено количество попыток входа!", false);
                }
            }
        });

        $form->setTitle("§l§6VelAuth §r§8| §fВход");
        $form->addLabel("§7С возвращением! Введите пароль для входа.");
        $form->addInput("§fВведите пароль:", "Ваш пароль");
        
        $player->sendForm($form);
    }
}
