<?php

namespace LDTO\Attr;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class JsonString
{
  public function __construct(
    public readonly bool $emptyItemIsArray = false,
    public readonly int $jsonFlag = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
  ) {
  }
}
