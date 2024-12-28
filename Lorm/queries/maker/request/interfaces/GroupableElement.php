<?php

namespace Lorm\queries\maker\request\interfaces;

interface GroupableElement
{
  /**
   * Decodes a group
   * @param array $group
   */
  public static function decode_group($group): string;
}
