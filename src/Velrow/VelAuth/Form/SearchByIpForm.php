<?php

declare(strict_types=1);

namespace Velrow\VelAuth\Form;

use pocketmine\player\Player;
use jojoe77777\FormAPI\CustomForm;

final class SearchByIpForm {
    public static function send(Player $player): void {
        $form = new CustomForm(function (Player $player, ?array $data): void {
            if ($data === null) {
                AdminMenuForm::send($player);
                return;
            }

            $ip = $data[1] ?? "";
            
            if (empty($ip)) {
                $player->sendMessage("§cВведите IP адрес!");
                self::send($player);
                return;
            }

            LinkedAccountsForm::send($player, $ip);
        });

        $form->setTitle("§l§6VelAuth §r§8| §fПоиск по IP");
        $form->addLabel("§7Введите IP адрес для поиска связанных аккаунтов");
        $form->addInput("§fIP адрес:", "127.0.0.1");
        
        $player->sendForm($form);
    }
}
