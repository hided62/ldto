<?php

namespace LDTO\Converter;

use InvalidArgumentException;

class DateTimeConverter implements Converter
{
  const YMD_HIS = 'Y-m-d H:i:s';
  const YMD_HISU = 'Y-m-d H:i:s.u';

  protected \DateTimeZone $timeZoneOffset;


  /**
   * @param string[] $types
   * @param string|int|float|null $timezone
   * @param string $datetimeFormat
   * @return void
   * @throws InvalidArgumentException
   */
  public function __construct(private array $types, string|int|float|null $timezone = null, public readonly string $datetimeFormat = self::YMD_HIS)
  {
    $this->timeZoneOffset = static::extractDateTimeZone($timezone);
  }

  private static function extractDateTimeZone(string|int|float|null $timezone): \DateTimeZone
  {
    if ($timezone === null) {
      return new \DateTimeZone(date_default_timezone_get());
    }

    if (is_int($timezone)) {
      if ($timezone < -12 || $timezone > 14) {
        throw new \InvalidArgumentException('TimeZone argument must be between -12 and 14');
      }
      if ($timezone > 0) {
        $offset = sprintf('+%02d00', $timezone);
      } else {
        $offset = sprintf('-%02d00', abs($timezone));
      }
      return new \DateTimeZone($offset);
    }

    if (is_float($timezone)) {
      if ($timezone < -12 || $timezone > 14) {
        throw new \InvalidArgumentException('TimeZone argument must be between -12 and 14');
      }
      $isPositive = $timezone > 0;
      $offset = abs($timezone);
      $hour = floor($offset);
      $minute = floor(($offset - $hour) * 60);
      if ($isPositive) {
        $offset = sprintf('+%02d%02d', $hour, $minute);
      } else {
        $offset = sprintf('-%02d%02d', $hour, $minute);
      }
      return new \DateTimeZone($offset);
    }

    return  new \DateTimeZone($timezone);
  }

  public function convertFrom(string|array|int|float|bool|null $raw, string $name): mixed
  {
    if ($raw === null && array_search('null', $this->types, true) !== false) {
      return null;
    }
    if (!is_string($raw)) {
      throw new \InvalidArgumentException("DateTimeConverter can not convert non-string: {$name}");
    }
    if (array_search('DateTimeImmutable', $this->types, true) !== false) {
      $objDateTime = new \DateTimeImmutable($raw, $this->timeZoneOffset);
    } else if (array_search('DateTime', $this->types, true) !== false) {
      $objDateTime = new \DateTime($raw, $this->timeZoneOffset);
    } else {
      $objDateTime = new \DateTimeImmutable($raw, $this->timeZoneOffset);
    }

    if ($objDateTime->getTimezone() !== $this->timeZoneOffset) {
      $objDateTime = $objDateTime->setTimezone($this->timeZoneOffset);
    }
    return $objDateTime;
  }

  public function convertTo(mixed $data): string|array|int|float|bool|null
  {
    if ($data === null && array_search('null', $this->types, true) !== false) {
      return null;
    }
    if (!$data instanceof \DateTimeInterface) {
      throw new \Exception('DateTimeConverter can not convert non-DateTimeInterface');
    }

    if ($data->getTimezone() !== $this->timeZoneOffset) {
      $data = \DateTimeImmutable::createFromInterface($data)->setTimezone($this->timeZoneOffset);
    }
    return $data->format($this->datetimeFormat);
  }
}
