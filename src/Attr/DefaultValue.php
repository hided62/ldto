<?php

namespace LDTO\Attr;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class DefaultValue
{
  public function __construct(public readonly null|bool|int|float|string|array $defaultValue)
  {
  }

  public function getDefaultValue(): mixed
  {
    return $this->defaultValue;
  }
}
