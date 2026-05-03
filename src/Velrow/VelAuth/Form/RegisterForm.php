<?php

declare(strict_types=1);

namespace Velrow\VelAuth\Form;

use pocketmine\player\Player;
use jojoe77777\FormAPI\CustomForm;
use Velrow\VelAuth\VelAuth;

final class RegisterForm {
    public static function send(Player $player): void {
        $form = new CustomForm(function (Player $player, ?array $data): void {
            if ($data === null) {
                self::send($player);
                return;
            }

            $password = $data[1] ?? "";
            $confirmPassword = $data[2] ?? "";

            if (empty($password) || empty($confirmPassword)) {
                $player->sendMessage("§cВсе поля обязательны для заполнения!");
                self::send($player);
                return;
            }

            if ($password !== $confirmPassword) {
                $player->sendMessage("§cПароли не совпадают!");
                self::send($player);
                return;
            }

            $authManager = VelAuth::getInstance()->getAuthManager();
            
            if ($authManager->register($player, $password)) {
                $authManager->login($player, $password);
                $authManager->createSession($player, 86400 * 7);
                $player->sendMessage("§aРегистрация успешна! Добро пожаловать!");
            } else {
                $player->sendMessage("§cОшибка регистрации. Пароль должен быть от 6 до 32 символов.");
                self::send($player);
            }
        });

        $form->setTitle("§l§6VelAuth §r§8| §fРегистрация");
        $form->addLabel("§7Добро пожаловать! Создайте аккаунт для продолжения.\n§7Пароль должен содержать от 6 до 32 символов.");
        $form->addInput("§fВведите пароль:", "Минимум 6 символов");
        $form->addInput("§fПодтвердите пароль:", "Повторите пароль");
        
        $player->sendForm($form);
    }
}
