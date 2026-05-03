<?php

declare(strict_types=1);

namespace Velrow\VelAuth\Form;

use pocketmine\player\Player;
use jojoe77777\FormAPI\CustomForm;
use Velrow\VelAuth\VelAuth;

final class ChangePasswordForm {
    public static function send(Player $player): void {
        $form = new CustomForm(function (Player $player, ?array $data): void {
            if ($data === null) {
                ProfileMenuForm::send($player);
                return;
            }

            $oldPassword = $data[1] ?? "";
            $newPassword = $data[2] ?? "";
            $confirmPassword = $data[3] ?? "";

            if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
                $player->sendMessage("§cВсе поля обязательны для заполнения!");
                self::send($player);
                return;
            }

            if ($newPassword !== $confirmPassword) {
                $player->sendMessage("§cНовые пароли не совпадают!");
                self::send($player);
                return;
            }

            $authManager = VelAuth::getInstance()->getAuthManager();
            
            if ($authManager->changePassword($player, $oldPassword, $newPassword)) {
                $player->sendMessage("§aПароль успешно изменен!");
                ProfileMenuForm::send($player);
            } else {
                $player->sendMessage("§cНеверный старый пароль или новый пароль не соответствует требованиям!");
                self::send($player);
            }
        });

        $form->setTitle("§l§6VelAuth §r§8| §fСмена пароля");
        $form->addLabel("§7Введите текущий и новый пароль.\n§7Новый пароль должен содержать от 6 до 32 символов.");
        $form->addInput("§fТекущий пароль:", "Ваш текущий пароль");
        $form->addInput("§fНовый пароль:", "Минимум 6 символов");
        $form->addInput("§fПодтвердите новый пароль:", "Повторите новый пароль");
        
        $player->sendForm($form);
    }
}
