<?php /** @noinspection PhpUnused */

namespace EnderPearlCD;

use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\EnderPearl;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Base extends PluginBase
{
    use SingletonTrait;

    public array $cooldown = [];

    protected function onLoad(): void
    {
        self::setInstance($this);
        $this->saveDefaultConfig();
    }

    protected function onEnable(): void
    {
        $config = $this->getConfig();

        $this->getServer()->getPluginManager()->registerEvent(PlayerItemUseEvent::class, function (PlayerItemUseEvent $event) use ($config): void {

            $player = $event->getPlayer();
            $item = $event->getItem();

            if ($item instanceof EnderPearl) {
                if (isset($this->cooldown[$player->getName()]) && ($cooldown = $this->cooldown[$player->getName()]) > time()) {
                    $this->send($player, $config->get("in-cooldown-type"), str_replace("{time}", intval($cooldown - time()), $config->get("in-cooldown-message")));
                    $event->cancel();
                    return;
                }

                if ((!$config->get("creative-config") && !$player->isCreative())) {
                    $this->cooldown[$player->getName()] = microtime(true) + $config->get("cooldown");

                    if ($config->get("experience-cooldown")) {
                        $this->getScheduler()->scheduleRepeatingTask(new ExperienceTask($player, $config->get("cooldown")), $config->get("update-experience"));
                    }
                }
            }
        }, EventPriority::MONITOR, $this);
    }

    public function send(Player $player, string $type, string $message): void
    {
        switch ($type) {
            case "toast":
                $player->sendToastNotification("EnderPearl", $message);
                break;
            case "popup":
                $player->sendPopup($message);
                break;
            case "message":
                $player->sendMessage($message);
                break;
        }
    }
}