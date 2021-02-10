<?php

namespace discord3682\tag\cmd;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\Permission;
use pocketmine\Player;

use discord3682\tag\STag;
use discord3682\tag\Tag;

class TagListCommand extends Command
{

  public function __construct ()
  {
    parent::__construct ('태그목록', '태그 목록을 봅니다.', '태그목록 [페이지]', [
      'taglist'
    ]);
    $this->setPermission ('op');
  }

  public function execute (CommandSender $sender, string $commandLabel, array $args)
  {
    if (!$this->testPermission ($sender)) return;
    if (!isset ($args [0]) or !is_numeric ($args [0]))
      $args [0] = 1;

    $max = count (STag::getTags ());
    $maxPage = ceil ($max / 5);
    $args [0] = max ($args [0], 1);
    $args [0] = min ($maxPage, $args [0]);

    STag::msg ($sender, '---------- [ 태그 목록 : §b' . $args [0] . ' / ' . $maxPage . ' §7] ----------');
    for ($i = 1; $i <= 5; $i ++)
    {
      $index = (5 * ($args [0] - 1)) + $i;
      if ($index > $max) break;
      $tag = STag::getTag ($index - 1);
      if (!$tag instanceof Tag) continue;
      $str = mb_substr ($tag->getText (), 0, 8, 'UTF-8');
      STag::msg ($sender, $index . '. ' . $tag->getPositionString () . ' - ' . $str);
    }
  }
}
