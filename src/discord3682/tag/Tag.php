<?php

namespace discord3682\tag;

use pocketmine\level\Position;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\entity\Entity;
use pocketmine\utils\UUID;
use pocketmine\Player;
use pocketmine\Server;

class Tag
{

  protected $position;
  protected $text;
  protected $distance;

  protected $sendPk;
  protected $removePk;

  protected $send = [];

  private $folderName = '';

  public function __construct (string $position, string $text, int $distance)
  {
    $this->position = $position;
    $this->text = $text;
    $this->distance = $distance;

    $this->folderName = $this->getPosition ()->level->getFolderName ();

    $this->sendPk = new AddPlayerPacket ();
    $id = Entity::$entityCount++;
    $this->id = $id;
    $this->sendPk->entityRuntimeId = $id;
    $this->sendPk->entityUniqueId = $id;
    $this->sendPk->position = $this->getPosition ()->add (0, 0.75, 0);
    $this->sendPk->username = str_replace ('(n)', "\n", $this->text);
    $this->sendPk->uuid = UUID::fromRandom ();
    $this->sendPk->item = ItemFactory::get (Item::AIR, 0, 0);
    $flags = (1 << Entity::DATA_FLAG_IMMOBILE);
    $this->sendPk->metadata = [
      Entity::DATA_FLAGS => [
        Entity::DATA_TYPE_LONG,
        $flags
      ],
      Entity::DATA_SCALE => [
        Entity::DATA_TYPE_FLOAT,
        0.01
      ]
    ];

    $this->removePk = new RemoveActorPacket();
    $this->removePk->entityUniqueId = $id;
  }

  public function getFolderName () : string
  {
    return $this->folderName;
  }

  public function getPosition () : ?Position
  {
    $pos = explode (':', $this->position);
    $pos = new Position ($pos [0], $pos [1], $pos [2], Server::getInstance ()->getLevelByName ($pos [3]));
    return $pos;
  }

  public function getPositionString () : string
  {
    return $this->position;
  }

  public function sendTag (Player $player)
  {
    if (!isset ($this->send [convert ($player)]))
    {
      $this->send [convert ($player)] = true;
      $player->sendDataPacket (clone $this->sendPk);
    }
  }

  public function removeTag (Player $player)
  {
    if (isset ($this->send [convert ($player)]))
    {
      unset ($this->send [convert ($player)]);
      $player->sendDataPacket (clone $this->removePk);
    }
  }

  public function getText () : string
  {
    return $this->text;
  }

  public function setText (string $text)
  {
    $this->text = $text;

    foreach ($this->send as $playerName => $bool)
    {
      $player = Server::getInstance ()->getPlayer ($playerName);
      if ($player instanceof Player)
      {
        $this->removeTag ($player);
        $this->sendTag ($player);
      }else
      {
        unset ($this->send [$playerName]);
      }
    }
  }

  public function getDistance () : int
  {
    return (int) $this->distance;
  }

  public function setDistance (int $distance)
  {
    $this->distance = (int) $distance;
  }

  public function serialize () : array
  {
    return array ($this->position, $this->text, $this->distance);
  }

  public static function deserialize (array $data) : Tag
  {
    return new Tag (...$data);
  }
}
