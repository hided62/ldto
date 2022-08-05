<?php

namespace LDTO\Attr;

use Exception;
use LDTO\Converter\Converter;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS)]
class Convert
{
  public Converter $converter;
  /** @var string[] */
  public array $targetTypes;
  /** @var array<mixed> */
  public readonly array $args;

  /**
   * @param string $converterType
   * @param array<mixed> $args
   * @return void
   * @throws Exception
   */
  public function __construct(
    public readonly string $converterType,
    ...$args
  ) {
    if(!is_subclass_of($converterType, \LDTO\Converter\Converter::class)){
      throw new \Exception("$converterType is not a subclass of DTO\Converter\Converter");
    }
    $this->args = $args;
  }

  /**
   * @param string[] $targetTypes
   */
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
