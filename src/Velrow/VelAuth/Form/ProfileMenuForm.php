<?php

declare(strict_types=1);

namespace Velrow\VelAuth\Form;

use pocketmine\player\Player;
use jojoe77777\FormAPI\CustomForm;
use Velrow\VelAuth\VelAuth;

final class ProfileMenuForm {
    public static function send(Player $player): void {
        $database = VelAuth::getInstance()->getDatabaseManager();
        $autoLoginEnabled = $database->isAutoLoginEnabled($player->getName());
        
        $form = new CustomForm(function (Player $player, ?array $data): void {
            if ($data === null) {
                MainMenuForm::send($player);
                return;
            }

            $action = $data[1] ?? 0;
            $autoLogin = $data[2] ?? false;
            
            $database = VelAuth::getInstance()->getDatabaseManager();
            $database->setAutoLogin($player->getName(), $autoLogin);
            
            match ($action) {
                0 => ChangePasswordForm::send($player),
                1 => LogoutConfirmForm::send($player),
                default => MainMenuForm::send($player)
            };
        });

        $form->setTitle("§l§6VelAuth §r§8| §f" . $player->getName());
        $form->addLabel("§7Управление вашим аккаунтом");
        $form->addDropdown("§fВыберите действие:", ["Сменить пароль", "Выйти из аккаунта", "Назад"]);
        $form->addToggle("§fАвтовход по IP", $autoLoginEnabled);
        
        $player->sendForm($form);
    }
}
