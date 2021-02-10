<?php

namespace discord3682\tag;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\scheduler\ClosureTask;
use pocketmine\level\Level;
use pocketmine\Player;

class EventListener implements Listener
{

  private static $tasks = [];

  public function onPlayerJoin (PlayerJoinEvent $ev)
  {
    $player = $ev->getPlayer ();

    $checkTask = new ClosureTask (function (int $currentTick) use ($player) : void
    {
      STag::sendTags ($player);
    });

    STag::$instance->getScheduler ()->scheduleRepeatingTask ($checkTask, 10);
    self::$tasks [convert ($player)] = $checkTask->getHandler ();
  }

  public function onPlayerQuit (PlayerQuitEvent $ev)
  {
    $player = $ev->getPlayer ();

    if (isset (self::$tasks [convert ($player)]))
    {
      self::$tasks [convert ($player)]->cancel ();
      unset (self::$tasks [convert ($player)]);

      foreach (STag::getTagsByWorld ($player->level->getFolderName ()) as $tag)
      {
        $tag->removeTag ($player);
      }
    }
  }

  public function onEntityTeleport (EntityTeleportEvent $ev)
  {
    $entity = $ev->getEntity ();

    if (!$entity instanceof Player) return;

    $from = $ev->getFrom ();
    $to = $ev->getTo ();

    if (
      !$from->level instanceof Level or
      !$to->level instanceof Level
    ) return;
    if ($from->level->getFolderName () === $to->level->getFolderName ()) return;

    foreach (STag::getTagsByWorld ($from->level->getFolderName ()) as $tag)
    {
      $tag->removeTag ($entity);
    }
  }
}
