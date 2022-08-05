<?php

namespace LDTO\Converter;

use Exception;

class MapConverter implements Converter
{
  private Converter $itemConverter;

  /**
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
    else if (!is_subclass_of($itemConverterClass, Converter::class)) {
      throw new \Exception("$itemConverterClass is not a subclass of \LDTO\Converter\Converter");
    }
    $this->itemConverter = new $itemConverterClass($itemTypes, ...$args);
  }

  public function convertFrom(string|array|int|float|bool|null $raw, string $name): mixed
  {
    if ($raw === null && array_search('null', $this->types, true) !== false) {
      return null;
    }
    if (!is_array($raw)) {
      throw new \Exception("value is not a array: $name");
    }

    $result = [];
    foreach ($raw as $key => $value) {
      $result[$key] = $this->itemConverter->convertFrom($value, $name);
    }
    return $result;
  }

  public function convertTo(mixed $data): string|array|int|float|bool|null
  {
    if ($data === null && array_search('null', $this->types, true) !== false) {
      return null;
    }
    if (!is_array($data) && !($data instanceof \Traversable)) {
      throw new \Exception('value is not a array');
    }
    $result = [];
    foreach ($data as $key => $value) {
      $result[$key] = $this->itemConverter->convertTo($value);
    }
    return $result;
  }
}
