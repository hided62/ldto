<?php

namespace LDTO\Attr;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class NullIsUndefined
{
  public function __construct()
  {
  }
}
