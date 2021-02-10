<?php

namespace discord3682\tag\cmd;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\Permission;
use pocketmine\Player;

use discord3682\tag\STag;

class AddTagCommand extends Command
{

  public function __construct ()
  {
    parent::__construct ('태그추가', '태그를 제거합니다.', '태그생성 [문자] [인식범위]', [
      '태그생성',
      'addtag'
    ]);
    $this->setPermission ('op');
  }

  public function execute (CommandSender $sender, string $commandLabel, array $args)
  {
    if ($sender instanceof Player)
    {
      if (!$this->testPermission ($sender)) return;
      if (!isset ($args [0]))
      {
        STag::msg ($sender, '문자를 입력하여 주십시오.');
      }elseif (!isset ($args [1]))
      {
        STag::msg ($sender, '인식범위를 입력하여 주십시오.');
      }else
      {
        if (is_numeric ($args [1]))
        {
          $distance = (int) $args [1];
          unset ($args [1]);
          $text = implode (' ', $args);
          if (STag::addTag ($sender->getPosition (), $text, $distance))
          {
            STag::msg ($sender, '태그를 추가하셨습니다.');
          }else
          {
            STag::msg ($sender, '해당 위치엔 이미 태그가 존재합니다.');
          }
        }else
        {
          STag::msg ($sender, '인식범위는 숫자로 입력하여 주십시오.');
        }
      }
    }
  }
}
