<?php
namespace LDTO\Util;

class DTOUtil{
  private function __construct()
  {

  }

  /**
   * @return array<string,\ReflectionAttribute>
   */
  public static function getAttrs(\ReflectionProperty $prop): array{
    $result = [];
    foreach($prop->getAttributes() as $attr){
      $result[$attr->getName()] = $attr;
    }
    return $result;
  }

  /**
   * @return array<string,\ReflectionParameter>
   */
  public static function getConstructorParams(\ReflectionClass $class): array{
    $constructor = $class->getConstructor();
    if($constructor === null){
      return [];
    }
    $result = [];
    foreach($constructor->getParameters() as $param){
      $result[$param->getName()] = $param;
    }
    return $result;
  }

  /**
   * @return string[]
   */
  public static function getPropTypes(\ReflectionProperty $prop): array{
    $result = [];
    $type = $prop->getType();
    if($type === null){
      throw new \Exception("Property {$prop->getName()} has no type");
    }

    if($type->allowsNull()){
      $result[] = 'null';
    }

    if($type instanceof \ReflectionIntersectionType){
      throw new \Exception("Intersection types are not supported");
    }

    if($type instanceof \ReflectionNamedType){
      $result[] = $type->getName();
      return $result;
    }

    if($type instanceof \ReflectionUnionType){
      foreach($type->getTypes() as $type){
        $result[] = $type->getName();
      }
      return $result;
    }

    throw new \Exception("Unknown type {$type->getName()}");
  }
}