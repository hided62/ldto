<?php

namespace hided62\LDTO\Attr;

use hided62\LDTO\Converter\Converter;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS)]
class Convert
{
  public Converter $converter;
  public array $targetTypes;
  public readonly array $args;
  public function __construct(
    public readonly string $converterType,
    ...$args
  ) {
    if(!is_subclass_of($converterType, \hided62\LDTO\Converter\Converter::class)){
      throw new \Exception("$converterType is not a subclass of DTO\Converter\Converter");
    }
    $this->args = $args;
  }

  public function setType(array $targetTypes): self{
    $this->targetTypes = $targetTypes;
    $converterType = $this->converterType;
    $this->converter = new $converterType($targetTypes, ...$this->args);
    return $this;
  }

  public function convertFrom(string|array|int|float|bool|null $raw, string $name): mixed
  {
    if($this->converter === null){
      throw new \Exception("converter[{$name}] is not set");
    }
    return $this->converter->convertFrom($raw, $name);
  }

  public function convertTo(mixed $target): string|array|int|float|bool|null {
    if($this->converter === null){
      throw new \Exception('converter is not set');
    }
    return $this->converter->convertTo($target);
  }
}
