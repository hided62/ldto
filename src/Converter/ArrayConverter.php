<?php

namespace hided62\LDTO\Converter;

class ArrayConverter implements Converter
{
  private Converter $itemConverter;
  public function __construct(private array $types, array $itemTypes, ?string $itemConverterClass = null, ...$args)
  {
    if(!is_array($itemTypes)){
      throw new \Exception('itemTypes is not a array');
    }
    if($itemConverterClass === null){
      $itemConverterClass = DefaultConverter::class;
    }
    else if(!is_subclass_of($itemConverterClass, Converter::class)){
      throw new \Exception("$itemConverterClass is not a subclass of \hided62\LDTO\Converter\Converter");
    }
    $this->itemConverter = new $itemConverterClass($itemTypes, ...$args);
  }

  public function convertFrom(string|array|int|float|bool|null $raw, string $name): mixed
  {
    if ($raw === null && array_search('null', $this->types, true) !== false) {
      return null;
    }
    if (!is_array($raw) || !array_is_list($raw)) {
      throw new \Exception("value is not a array: $name");
    }
    return array_map(fn ($v) => $this->itemConverter->convertFrom($v, $name), $raw);
  }

  public function convertTo(mixed $data): string|array|int|float|bool|null
  {
    if ($data === null && array_search('null', $this->types) !== false) {
      return null;
    }
    if (!is_array($data) || !array_is_list($data)) {
      throw new \Exception('value is not a array');
    }
    return array_map(fn ($v) => $this->itemConverter->convertTo($v), $data);
  }
}
