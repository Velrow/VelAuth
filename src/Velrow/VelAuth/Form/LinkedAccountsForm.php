<?php

declare(strict_types=1);

namespace Velrow\VelAuth\Form;

use pocketmine\player\Player;
use jojoe77777\FormAPI\SimpleForm;
use Velrow\VelAuth\VelAuth;

final class LinkedAccountsForm {
    public static function send(Player $player, string $ip): void {
        $database = VelAuth::getInstance()->getDatabaseManager();
        $accounts = $database->getPlayersByIp($ip);
        
        $form = new SimpleForm(function (Player $player, ?int $data): void {
            AdminMenuForm::send($player);
        });

        $accountsList = implode("\n", array_map(fn($acc) => "§f• {$acc}", $accounts));
        
        $form->setTitle("§l§6VelAuth §r§8| §fСвязанные аккаунты");
        $form->setContent(
            "§7IP: §f{$ip}\n" .
            "§7Найдено аккаунтов: §e" . count($accounts) . "\n\n" .
            $accountsList
        );
        $form->addButton("§l§c◀ Назад", 0, "textures/ui/cancel");
        
        $player->sendForm($form);
    }
}
