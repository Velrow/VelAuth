<?php

declare(strict_types=1);

namespace Velrow\VelAuth\Form;

use pocketmine\player\Player;
use jojoe77777\FormAPI\SimpleForm;
use Velrow\VelAuth\VelAuth;

final class PlayerManageForm {
    public static function send(Player $player, string $targetPlayer): void {
        $database = VelAuth::getInstance()->getDatabaseManager();
        $playerData = $database->getPlayerData($targetPlayer);
        
        if ($playerData === null) {
            $player->sendMessage("§cИгрок не найден!");
            AdminMenuForm::send($player);
            return;
        }

        $lastIp = $playerData["last_ip"] ?? "Неизвестно";
        $lastLogin = $playerData["last_login"] ? date("d.m.Y H:i", $playerData["last_login"]) : "Никогда";
        $registered = date("d.m.Y H:i", $playerData["registered_at"]);
        
        $linkedAccounts = $database->getPlayersByIp($lastIp);
        $linkedCount = count($linkedAccounts) - 1;
        
        $form = new SimpleForm(function (Player $player, ?int $data) use ($targetPlayer, $lastIp): void {
            if ($data === null || $data === 2) {
                PlayerListForm::send($player);
                return;
            }

            match ($data) {
                0 => AdminChangePasswordForm::send($player, $targetPlayer),
                1 => LinkedAccountsForm::send($player, $lastIp),
                default => null
            };
        });

        $form->setTitle("§l§6VelAuth §r§8| §f" . $targetPlayer);
        $form->setContent(
            "§7Последний IP: §f{$lastIp}\n" .
            "§7Последний вход: §f{$lastLogin}\n" .
            "§7Регистрация: §f{$registered}\n" .
            "§7Связанных аккаунтов: §e{$linkedCount}"
        );
        $form->addButton("§l§e Сменить пароль\n§r§7Изменить пароль игрока", 0, "textures/ui/lock_color");
        $form->addButton("§l§b Связанные аккаунты\n§r§7Показать все аккаунты с этого IP", 0, "textures/ui/magnifyingGlass");
        $form->addButton("§l§c◀ Назад", 0, "textures/ui/cancel");
        
        $player->sendForm($form);
    }
}
