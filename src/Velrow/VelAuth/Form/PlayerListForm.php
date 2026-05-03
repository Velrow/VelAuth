<?php

declare(strict_types=1);

namespace Velrow\VelAuth\Form;

use pocketmine\player\Player;
use jojoe77777\FormAPI\SimpleForm;
use Velrow\VelAuth\VelAuth;

final class PlayerListForm {
    public static function send(Player $player): void {
        $database = VelAuth::getInstance()->getDatabaseManager();
        $players = $database->getAllPlayers();
        
        $form = new SimpleForm(function (Player $player, ?int $data) use ($players): void {
            if ($data === null || $data === count($players)) {
                AdminMenuForm::send($player);
                return;
            }

            $selectedPlayer = $players[$data];
            PlayerManageForm::send($player, $selectedPlayer);
        });

        $form->setTitle("§l§6VelAuth §r§8| §fСписок игроков");
        $form->setContent("§7Всего игроков: §e" . count($players));
        
        foreach ($players as $playerName) {
            $form->addButton("§l§f" . $playerName . "\n§r§7Нажмите для управления", 0, "textures/ui/icon_steve");
        }
        
        $form->addButton("§l§c◀ Назад", 0, "textures/ui/cancel");
        
        $player->sendForm($form);
    }
}
