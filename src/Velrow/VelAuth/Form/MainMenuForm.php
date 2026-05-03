<?php

declare(strict_types=1);

namespace Velrow\VelAuth\Form;

use pocketmine\player\Player;
use jojoe77777\FormAPI\SimpleForm;

final class MainMenuForm {
    public static function send(Player $player): void {
        $isAdmin = $player->hasPermission("velauth.admin");
        
        $form = new SimpleForm(function (Player $player, ?int $data): void {
            if ($data === null) {
                return;
            }

            $isAdmin = $player->hasPermission("velauth.admin");
            
            if ($data === 0) {
                ProfileMenuForm::send($player);
            } elseif ($data === 1 && $isAdmin) {
                AdminMenuForm::send($player);
            }
        });

        $form->setTitle("§l§6VelAuth");
        $form->setContent("§7Добро пожаловать, §f" . $player->getName() . "§7!");
        $form->addButton("§l§b👤 " . $player->getName() . "\n§r§7Личный кабинет", 0, "textures/ui/icon_steve");
        
        if ($isAdmin) {
            $form->addButton("§l§c⚙ Управление\n§r§7Панель администратора", 0, "textures/ui/gear");
        }
        
        $player->sendForm($form);
    }
}
