<?php

declare(strict_types=1);

namespace Velrow\VelAuth\Form;

use pocketmine\player\Player;
use jojoe77777\FormAPI\SimpleForm;

final class AdminMenuForm {
    public static function send(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data): void {
            if ($data === null) {
                MainMenuForm::send($player);
                return;
            }

            match ($data) {
                0 => PlayerListForm::send($player),
                1 => SearchByIpForm::send($player),
                2 => MainMenuForm::send($player),
                default => null
            };
        });

        $form->setTitle("§l§6VelAuth §r§8| §cАдмин-панель");
        $form->setContent("§7Управление игроками");
        $form->addButton("§l§e Список игроков\n§r§7Управление аккаунтами", 0, "textures/ui/book_writable");
        $form->addButton("§l§b Поиск по IP\n§r§7Найти связанные аккаунты", 0, "textures/ui/magnifyingGlass");
        $form->addButton("§l§c Назад\n§r§7Вернуться в меню", 0, "textures/ui/cancel");
        
        $player->sendForm($form);
    }
}
