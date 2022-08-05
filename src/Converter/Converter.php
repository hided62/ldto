<?php
namespace LDTO\Converter;

interface Converter{
  public function convertFrom(string|array|int|float|bool|null $raw, string $name): mixed;
  public function convertTo(mixed $data): string|array|int|float|bool|null;
}