<?php

namespace LDTO\Converter;

use Exception;

class ArrayConverter implements Converter
{
  private Converter $itemConverter;

  /**
   *
   * @param string[] $types
   * @param string[] $itemTypes
   * @param null|string $itemConverterClass
   * @param mixed[] $args
   * @return void
   * @throws Exception
   */
  public function __construct(private array $types, array $itemTypes, ?string $itemConverterClass = null, ...$args)
  {
    if(!is_array($itemTypes)){
      throw new \Exception('itemTypes is not a array');
    }
    if($itemConverterClass === null){
      $itemConverterClass = DefaultConverter::class;
    }
    else if(!is_subclass_of($itemConverterClass, Converter::class)){
      throw new \Exception("$itemConverterClass is not a subclass of \LDTO\Converter\Converter");
    }
    $this->itemConverter = new $itemConverterClass($itemTypes, ...$args);
  }

  /**
   *
   * @param string|array<string|int|float|bool|null>|int|float|bool|null $raw
   * @param string $name
   * @throws Exception
   */
  public function convertFrom(string|array|int|float|bool|null $raw, string $name): mixed
  {
    if ($raw === null && array_search('null', $this->types, true) !== false) {
      return null;
    }
    if (!is_array($raw) || !array_is_list($raw)) {
      throw new \Exception("value is not a array: $name");
    }
    return array_map(fn (string|array|int|float|bool|null $v): mixed => $this->itemConverter->convertFrom($v, $name), $raw);
  }

  public function convertTo(mixed $data): string|array|int|float|bool|null
  {
    if ($data === null && array_search('null', $this->types, true) !== false) {
      return null;
    }
    if (!is_array($data) || !array_is_list($data)) {
      throw new \Exception('value is not a array');
    }
    return array_map(fn (mixed $v): string|array|int|float|bool|null => $this->itemConverter->convertTo($v), $data);
  }
}
