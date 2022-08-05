<?php

namespace LDTO\Attr;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class DefaultValueGenerator
{
  public function __construct(public readonly string $generator)
  {
    if (!is_callable($generator)) {
      throw new \Exception("$generator is not a callable");
    }
  }

  public function getDefaultValue(): mixed
  {
    $generator = $this->generator;
    return $generator();
  }
}
