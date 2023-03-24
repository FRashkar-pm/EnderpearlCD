<?php

namespace EnderPearlCD;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;

class ExperienceTask extends Task
{
    public function __construct(private Player $player, private int $cooldown)
    {
    }

    public function onRun(): void
    {
        $player = $this->player;
        $cooldown = Base::getInstance()->cooldown[$player->getName()] ?? 0;

        if (!$player->isConnected() || !$player->isAlive() || time() > $cooldown) {
            if ($player instanceof Player) {
                $config = Base::getInstance()->getConfig();
                Base::getInstance()->send($player, $config->get("ended-cooldown-type"), $config->get("ended-cooldown-message"));

                $player->getXpManager()->setXpAndProgress(0, 0);
            }

            $this->getHandler()->cancel();
            return;
        }

        $progress = $cooldown - microtime(true);
        $player->getXpManager()->setXpAndProgress(intval($cooldown - time()), max(0, $progress / $this->cooldown));
    }
}
