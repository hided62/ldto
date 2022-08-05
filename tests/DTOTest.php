<?php
require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

use LDTO\Attr\Convert;
use LDTO\Attr\DefaultValue;
use LDTO\Attr\DefaultValueGenerator;
use LDTO\Attr\Ignore;
use LDTO\Attr\JsonString;
use LDTO\Attr\NullIsUndefined;
use LDTO\Attr\RawName;
use LDTO\Converter\Converter;
use LDTO\Converter\DateTimeConverter;
use LDTO\Converter\MapConverter;
use LDTO\Converter\ArrayConverter;
use LDTO\DTO;

class TypeA extends DTO
{
  public function __construct(
    public string $a,
    public string $b,
    public bool $c,
    public ?int $d,
    public float $e,
  ) {
  }
}

class TypeBA extends DTO
{
  public function __construct(
    public int $ba1,
    public int $ba2,
  ) {
  }
}

class TypeB extends DTO
{
  public function __construct(
    public string $a,
    public TypeBA $ba,
  ) {
  }
}

class TypeC extends DTO
{
  public function __construct(
    public string $a,
    #[JsonString]
    public TypeBA $ba,
  ) {
  }
}

enum EnumA
{
  case A;
  case B;
  case cc;
}

class TypeD extends DTO
{
  public function __construct(
    public ?string $a,
    public EnumA $b,
  ) {
  }
}

class TypeE extends DTO
{
  public function __construct(
    public ?string $a,
    #[NullIsUndefined]
    public ?string $b,
  ) {
  }
}

class ConverterDouble implements Converter
{
  public function __construct(array $types, ...$args)
  {
  }
  public function convertFrom(string|array|int|float|bool|null $raw, string $name): mixed
  {
    return $raw * 2;
  }
  public function convertTo(mixed $data): string|array|int|float|bool|null
  {
    return $data / 2;
  }
}

class TypeF extends DTO
{
  public function __construct(
    public ?string $a,
    #[Convert(ConverterDouble::class)]
    public int $b,
  ) {
  }
}

class TypeArr extends DTO
{
  public function __construct(
    public array $a,
    public array $b,
  ) {
  }
}


class TypeArrConverter extends DTO
{
  public function __construct(
    public array $a,
    #[Convert(ArrayConverter::class, ['string'])]
    public array $b,
  ) {
  }
}


class TypeMap extends DTO
{
  public function __construct(
    public int $a,
    #[Convert(MapConverter::class, ['null', 'string'])]
    public array $b,
  ) {
  }
}

class TypeNestedMap extends DTO
{
  public function __construct(
    public int $a,
    #[Convert(MapConverter::class, ['null', TypeBA::class])]
    public array $b,
  ) {
  }
}

class TypeRawName extends DTO
{
  public function __construct(
    #[RawName('arg_name')]
    public int $argName,
    #[RawName('vID')]
    public int $vID,
  ) {
  }
}


class TypeDateTime extends DTO
{
  public function __construct(
    #[Convert(DateTimeConverter::class)]
    public \DateTimeImmutable $a,
    #[Convert(DateTimeConverter::class, 9, DateTimeConverter::YMD_HIS)]
    public \DateTime $b,
    #[Convert(DateTimeConverter::class, 8, DateTimeConverter::YMD_HISU)]
    public \DateTimeImmutable|\DateTime $c,
    #[Convert(DateTimeConverter::class)]
    public \DateTimeInterface $d,
  ) {
  }
}

function returnDateTime()
{
  return new \DateTimeImmutable('2022-04-01 00:00:00');
}

class TypeDefaultValue extends DTO
{
  public bool $g = true;

  public function __construct(
    public ?string $a,
    #[DefaultValue(false)]
    public bool $b,
    public int $c,
    #[DefaultValue([1,2,3])]
    public array $e,
    #[DefaultValueGenerator('returnDateTime')]
    #[Convert(DateTimeConverter::class)]
    public \DateTimeInterface $d,
    public int $f = 111, //Only if the default value is set after the contstruct
  ) {
  }
}

class TypeIgnore extends DTO
{
  #[Ignore]
  public int $c = 100;
  #[Ignore]
  public $d;
  #[Ignore]
  public $e;

  public function __construct(
    public int $a,
    public int $b,
  ) {
    $this->d = $a * 2;
  }
}

class DTOTest extends \PHPUnit\Framework\TestCase
{
  public function testBasic()
  {
    $rawType = [
      'a' => '123',
      'b' => 'aa',
      'c' => false,
      'd' => null,
      'e' => 123.123,
    ];
    $obj = TypeA::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testNested()
  {
    $rawType = [
      'a' => '123',
      'ba' => [
        'ba1' => 3,
        'ba2' => 4,
      ]
    ];
    $obj = TypeB::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testNestedJson()
  {
    $x = json_encode([
      'ba1' => 3,
      'ba2' => 4,
    ]);
    $rawType = [
      'a' => '123',
      'ba' => $x
    ];
    $obj = TypeC::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testEnum()
  {
    $rawType = [
      'a' => '123',
      'b' => 'B',
    ];
    $obj = TypeD::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testNull()
  {
    $rawType = [
      'a' => null,
    ];
    $obj = TypeE::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testConverter()
  {
    $rawType = [
      'a' => '123',
      'b' => 123,
    ];
    $obj = TypeF::fromArray($rawType);
    $this->assertEquals($obj->b, 246);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testNullIsUndefined()
  {
    $rawType = [
      'a' => null,
    ];
    $obj = TypeE::fromArray($rawType);
    $this->assertEquals($obj->a, null);
    $this->assertEquals($obj->b, null);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testArr()
  {
    $rawType = [
      'a' => [1, 2, 3],
      'b' => [4, 5, 6],
    ];
    $obj = TypeArr::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testArrConverterErr()
  {
    $this->expectException(Exception::class);
    $rawType = [
      'a' => [1, 2, 3],
      'b' => [4, 5, 6],
    ];
    $obj = TypeArrConverter::fromArray($rawType);
    $testType = $obj->toArray();


    $this->assertEquals($rawType, $testType);
  }

  public function testArrConverterErr2()
  {
    $this->expectException(Exception::class);
    $rawType = [
      'a' => [1, 2, 3],
      'b' => [4 => 6, 5 => 2, 6 => 3],
    ];
    $obj = TypeArrConverter::fromArray($rawType);
    $testType = $obj->toArray();


    $this->assertEquals($rawType, $testType);
  }

  public function testArrConverter()
  {
    $rawType = [
      'a' => [1, 2, 3],
      'b' => ['1', '2', '3'],
    ];
    $obj = TypeArrConverter::fromArray($rawType);
    $testType = $obj->toArray();


    $this->assertEquals($rawType, $testType);
  }

  public function testMap()
  {
    $rawType = [
      'a' => 1,
      'b' => [
        '1' => '1',
        '2' => null,
        'ba' => '3',
      ],
    ];
    $obj = TypeMap::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testNestedMap()
  {
    $rawType = [
      'a' => 1,
      'b' => [
        'aaa' => [
          'ba1' => 1,
          'ba2' => 2,
        ],
        'xed' => null,
        'ccc' => [
          'ba1' => 3,
          'ba2' => 4,
        ],
      ],
    ];
    $obj = TypeNestedMap::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testRawName()
  {
    $rawType = [
      'arg_name' => 1,
      'vID' => 2,
    ];
    $obj = TypeRawName::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testDateTime()
  {
    $rawType = [
      'a' => '2022-01-01 10:11:22',
      'b' => '2022-02-01T12:34:56.1234+09:00',
      'c' => '2022-03-01T00:00:00.1234+09:00',
      'd' => '2022-04-01',
    ];

    $testValue = [
      'a' => '2022-01-01 10:11:22',
      'b' => '2022-02-01 12:34:56',
      'c' => '2022-02-28 23:00:00.123400',
      'd' => '2022-04-01 00:00:00',
    ];
    $obj = TypeDateTime::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($testValue, $testType);
  }

  public function testDefaultValue()
  {
    $rawType = [
      'c' => 3,
    ];
    $testValue = [
      'g' => true,
      'a' => null,
      'b' => false,
      'c' => 3,
      'e' => [1, 2, 3],
      'd' => '2022-04-01 00:00:00',
      'f' => 111,
    ];
    $obj = TypeDefaultValue::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($testValue, $testType);
  }

  public function testIgnore(){
    $rawType = [
      'a' => 1,
      'b' => 2,
    ];
    $obj = TypeIgnore::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }
}
