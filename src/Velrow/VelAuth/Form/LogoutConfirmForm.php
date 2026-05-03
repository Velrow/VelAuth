<?php

declare(strict_types=1);

namespace Velrow\VelAuth\Form;

use pocketmine\player\Player;
use jojoe77777\FormAPI\SimpleForm;
use Velrow\VelAuth\VelAuth;

final class LogoutConfirmForm {
    public static function send(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data): void {
            if ($data === null || $data === 1) {
                ProfileMenuForm::send($player);
                return;
            }

            if ($data === 0) {
                $sessionManager = VelAuth::getInstance()->getSessionManager();
                $database = VelAuth::getInstance()->getDatabaseManager();
                
                $sessionManager->deauthenticate($player);
                $database->deleteSession($player->getName());
                
                $player->sendMessage("§aВы вышли из аккаунта!");
                LoginForm::send($player);
            }
        });

        $form->setTitle("§l§6VelAuth §r§8| §fПодтверждение");
        $form->setContent("§7Вы уверены, что хотите выйти из аккаунта?");
        $form->addButton("§l§aДа, выйти", 0, "textures/ui/confirm");
        $form->addButton("§l§cОтмена", 0, "textures/ui/cancel");
        
        $player->sendForm($form);
    }
}
