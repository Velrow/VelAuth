<?php

declare(strict_types=1);

namespace Velrow\VelAuth\Form;

use pocketmine\player\Player;
use jojoe77777\FormAPI\CustomForm;
use Velrow\VelAuth\VelAuth;
use Velrow\VelAuth\Utils\PasswordHasher;

final class AdminChangePasswordForm {
    public static function send(Player $player, string $targetPlayer): void {
        $form = new CustomForm(function (Player $player, ?array $data) use ($targetPlayer): void {
            if ($data === null) {
                PlayerManageForm::send($player, $targetPlayer);
                return;
            }

            $newPassword = $data[1] ?? "";
            $confirmPassword = $data[2] ?? "";

            if (empty($newPassword) || empty($confirmPassword)) {
                $player->sendMessage("§cВсе поля обязательны!");
                self::send($player, $targetPlayer);
                return;
            }

            if ($newPassword !== $confirmPassword) {
                $player->sendMessage("§cПароли не совпадают!");
                self::send($player, $targetPlayer);
                return;
            }

            $database = VelAuth::getInstance()->getDatabaseManager();
            $hashedPassword = PasswordHasher::hash($newPassword);
            
            if ($database->updatePassword($targetPlayer, $hashedPassword)) {
                $player->sendMessage("§aПароль игрока §e{$targetPlayer} §aуспешно изменен!");
                PlayerManageForm::send($player, $targetPlayer);
            } else {
                $player->sendMessage("§cОшибка при смене пароля!");
                self::send($player, $targetPlayer);
            }
        });

        $form->setTitle("§l§6VelAuth §r§8| §fСмена пароля");
        $form->addLabel("§7Смена пароля для игрока: §e{$targetPlayer}\n§7Новый пароль должен содержать от 6 до 32 символов.");
        $form->addInput("§fНовый пароль:", "Минимум 6 символов");
        $form->addInput("§fПодтвердите пароль:", "Повторите пароль");
        
        $player->sendForm($form);
    }
}
