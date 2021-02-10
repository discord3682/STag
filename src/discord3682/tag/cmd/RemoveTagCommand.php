<?php

namespace discord3682\tag\cmd;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\Permission;
use pocketmine\Player;

use discord3682\tag\STag;

class RemoveTagCommand extends Command
{

  public function __construct ()
  {
    parent::__construct ('태그제거', '태그를 제거합니다.', '태그제거 [번호]', [
      '태그삭제',
      'removetag'
    ]);
    $this->setPermission ('op');
  }

  public function execute (CommandSender $sender, string $commandLabel, array $args)
  {
    if (!$this->testPermission ($sender)) return;
    if (!isset ($args [0]))
    {
      STag::msg ($sender, '번호를 입력하여 주십시오.');
    }else
    {
      if (!is_numeric ($args [0]))
      {
        STag::msg ($sender, '번호는 숫자로 입력하여 주십시오.');
      }else
      {
        if (STag::removeTag ($args [0]))
        {
          STag::msg ($sender, '§b' . (int) $args [0] . '§7번 태그를 제거하셨습니다.');
        }else
        {
          STag::msg ($sender, '해당 번호의 태그를 찾을 수 없습니다.');
        }
      }
    }
  }
}
