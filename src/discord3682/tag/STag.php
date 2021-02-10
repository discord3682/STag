<?php

namespace discord3682\tag;

use pocketmine\plugin\PluginBase;
use pocketmine\level\Position;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\Server;

use discord3682\tag\cmd\AddTagCommand;
use discord3682\tag\cmd\RemoveTagCommand;
use discord3682\tag\cmd\TagListCommand;

function convert ($player) : string
{
  if ($player instanceof Player)
    return strtolower ($player->getName ());

  if (($player = Server::getInstance()->getPlayer ($player)) !== null)
    return strtolower ($player->getName ());

  return strtolower ($player);
}

class STag extends PluginBase
{

  private $tagData;

  protected static $tags = [];

  public static $instance = null;

  const TAG = '§l§b[태그]§r§7 ';

  public function onEnable () : void
  {
    $this->tagData = new Config ($this->getDataFolder () . 'Data.yml', Config::YAML);

    foreach ($this->tagData->getAll () as $data)
    {
      $tag = Tag::deserialize ($data);
      self::$tags [] = $tag;
    }

    $this->getServer ()->getPluginManager ()->registerEvents (new EventListener (), $this);
    $this->getServer ()->getCommandMap ()->registerAll ('discord3682', [
      new AddTagCommand (),
      new RemoveTagCommand (),
      new TagListCommand ()
    ]);

    self::$instance = $this;
  }

  public function onDisable () : void
  {
    $data = [];

    foreach (array_values (self::$tags) as $tag)
    {
      $data [] = $tag->serialize ();
    }

    $this->tagData->setAll ($data);
    $this->tagData->save ();
  }

  public static function msg ($player, string $msg) : void
  {
    $player->sendMessage (self::TAG . $msg);
  }

  public static function getTags () : array
  {
    return self::$tags;
  }

  public static function getTagsByWorld (string $world) : array
  {
    $tags = [];

    foreach (array_values (self::$tags) as $tag)
    {
      if ($tag instanceof Position)
      {
        if ($tag->getFolderName () === $world)
        {
          $tags [] = $tag;
        }
      }
    }

    return $tags;
  }

  public static function getTag ($index) : ?Tag
  {
    return self::$tags [$index] ?? null;
  }

  public static function addTag (Position $position, string $text, int $distance) : bool
  {
    $posString = $position->x . ':' . $position->y . ':' . $position->z . ':' . $position->level->getFolderName ();

    foreach (STag::getTags () as $tag)
    {
      if ($tag->getPositionString () === $posString)
        return false;
    }

    $tag = new Tag ($posString, $text, (int) $distance);
    self::$tags [] = $tag;
    return true;
  }

  public static function removeTag (int $index) : bool
  {
    if (($tag = self::getTag ($index - 1)) instanceof Tag)
    {
      foreach (Server::getInstance ()->getOnlinePlayers () as $player)
      {
        $tag->removeTag ($player);
      }

      unset (self::$tags [$index - 1]);
      return true;
    }
    return false;
  }

  public static function sendTags (Player $player)
  {
    foreach (self::$tags as $posString => $tag)
    {
      if ($tag->getFolderName () !== $player->level->getFolderName ()) continue;
      if ($tag->getPosition () instanceof Position)
      {
        if ($tag->getPosition ()->distance ($player) <= $tag->getDistance ())
        {
          $tag->sendTag ($player);
        }else
        {
          $tag->removeTag ($player);
        }
      }else
      {
        unset (self::$tags [$posString]);
      }
    }
  }
}
