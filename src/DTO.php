<?php

namespace LDTO;

use Ds\Set;
use Exception;
use LDTO\Attr\Convert;
use LDTO\Converter\DefaultConverter;

abstract class DTO
{
  public static function fromArray(\ArrayAccess|array $array): static
  {
    $reflection = new \ReflectionClass(static::class);

    if ($array instanceof \ArrayAccess) {
      $keyExists = static fn (string|int $key): bool => $array->offsetExists($key);
    } else {
      $keyExists = static fn (string|int $key): bool => array_key_exists($key, $array);
    }

    $params = Util\DTOUtil::getConstructorParams($reflection);

    $args = [];
    $lazyMap = [];

    foreach ($reflection->getProperties(
      \ReflectionProperty::IS_PUBLIC
    ) as $property) {
      $attrs = Util\DTOUtil::getAttrs($property);
      $name = $property->getName();
      $rawName = $name;

      $param = $params[$name] ?? null;

      if(array_key_exists(Attr\Ignore::class, $attrs)){
        if($param !== null){
          throw new \Exception("Property {$name} is ignored but has a constructor parameter");
        }
        continue;
      }

      if (array_key_exists(Attr\DefaultValueGenerator::class, $attrs)){
        /** @var Attr\DefaultValueGenerator */
        $defaultValueSetter = $attrs[Attr\DefaultValueGenerator::class]->newInstance();
      } else if (array_key_exists(Attr\DefaultValue::class, $attrs)) {
        /** @var Attr\DefaultValue */
        $defaultValueSetter = $attrs[Attr\DefaultValue::class]->newInstance();
      }
      else{
        $defaultValueSetter = null;
      }

      if (array_key_exists(Attr\RawName::class, $attrs)) {
        $rawAttr = $attrs[Attr\RawName::class];
        $attr = new Attr\RawName(...$rawAttr->getArguments());
        $rawName = $attr->rawName;
      }

      if (!$keyExists($rawName)) {
        if ($param !== null && $param->isOptional()){
          $defaultValue = $param->getDefaultValue();
        }
        else if ($property->hasDefaultValue()) {
          $defaultValue = $property->getDefaultValue();
        } else if($defaultValueSetter !== null){
          $defaultValue = $defaultValueSetter->getDefaultValue();
        } else if ($property->getType()->allowsNull()) {
          $defaultValue = null;
        } else {
          throw new \Exception("Missing property: {$name}");
        }

        if($param !== null){
          $args[$name] = $defaultValue;
        }
        else{
          $lazyMap[$name] = $defaultValue;
        }
        continue;
      }

      $value = $array[$rawName];

      if (array_key_exists(Attr\JsonString::class, $attrs)) {
        $rawAttr = $attrs[Attr\JsonString::class];
        $attr = new Attr\JsonString(...$rawAttr->getArguments());
        $value = json_decode($value, true, $attr->maxDepth, $attr->jsonFlag);
      }

      $propTypes = Util\DTOUtil::getPropTypes($property);
      if (array_key_exists(Attr\Convert::class, $attrs)) {
        $rawAttr = $attrs[Attr\Convert::class];
        $converter = new Convert(...$rawAttr->getArguments());
      } else {
        $converter = new Convert(DefaultConverter::class);
      }
      $value = $converter->setType($propTypes)->convertFrom($value, $name);

      if($param !== null){
        $args[$name] = $value;
      }
      else{
        $lazyMap[$name] = $value;
      }
    }

    $contructorArgs = [];
    foreach(array_keys($params) as $paramName){
      if(!array_key_exists($paramName, $args)){
        break;
      }
      $contructorArgs[] = $args[$paramName];
    }

    $object = $reflection->newInstanceArgs($contructorArgs);
    if($object === null){
      throw new \Exception("Failed to create object");
    }
    foreach($lazyMap as $name => $value){
      $object->{$name} = $value;
    }
    return $object;
  }

  /**
   * @return array<mixed>
   * @throws Exception
   */
  public function toArray(string ...$exceptKeys): array
  {
    $reflection = new \ReflectionClass($this::class);
    $result = [];

    $exceptKeySet = new Set($exceptKeys);

    foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
      $value = $property->getValue($this);
      $attrs = Util\DTOUtil::getAttrs($property);
      $name = $property->getName();

      if(array_key_exists(Attr\Ignore::class, $attrs)){
        continue;
      }

      if($exceptKeySet->contains($name)){
        continue;
      }

      if (array_key_exists(Attr\RawName::class, $attrs)) {
        $rawAttr = $attrs[Attr\RawName::class];
        $attr = new Attr\RawName(...$rawAttr->getArguments());
        $name = $attr->rawName;
      }

      $propTypes = Util\DTOUtil::getPropTypes($property);
      if (array_key_exists(Attr\Convert::class, $attrs)) {
        $converter = new Convert(...$attrs[Attr\Convert::class]->getArguments());
      } else {
        $converter = new Convert(DefaultConverter::class);
      }
      $value = $converter->setType($propTypes)->convertTo($value);

      if (array_key_exists(Attr\JsonString::class, $attrs)) {
        $rawAttr = $attrs[Attr\JsonString::class];
        $attr = new Attr\JsonString(...$rawAttr->getArguments());
        if($value === [] && $attr->emptyItemIsArray){
          $value = (object)null;
        }
        $value = json_encode($value, $attr->jsonFlag, $attr->maxDepth);
      }

      if ($value === null && array_key_exists(Attr\NullIsUndefined::class, $attrs)) {
        continue;
      }
      $result[$name] = $value;
    }
    return $result;
  }
}
