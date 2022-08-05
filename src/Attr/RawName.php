<?php

namespace LDTO\Attr;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class RawName
{
  public function __construct(public readonly string $rawName)
  {
  }
}
