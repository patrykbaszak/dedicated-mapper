<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Utils;

class NativeSimpleObject
{
    /**
     * @param string|array{timezone?: ?string, date?: ?string}|object{timezone?: ?string, date?: ?string}|null $value
     */
    public static function DateTimeConstructor(string|array|object $value = null): \DateTime
    {
        if (null === $value) {
            return new \DateTime();
        }
        if (is_string($value)) {
            return new \DateTime($value);
        }
        if (is_object($value)) {
            $value = (array) $value;
        }

        $timezone = $value['timezone'] ? self::DateTimeZoneConstructor($value['timezone']) : null;
        $date = $value['date'] ?? 'now';

        return new \DateTime($date, $timezone);
    }

    /**
     * @param string|array{timezone?: ?string}|object{timezone?: ?string}|null $value
     */
    public static function DateTimeZoneConstructor(string|array|object $value = null): \DateTimeZone
    {
        if (null === $value) {
            $timezone = (new \DateTime())->getTimezone();
            if (false === $timezone) {
                throw new \RuntimeException('Cannot get default timezone.');
            }

            return $timezone;
        }

        if (is_string($value)) {
            return new \DateTimeZone($value);
        }

        if (is_object($value)) {
            $value = (array) $value;
        }

        if (array_key_exists('timezone', $value)) {
            if (null === $value['timezone']) {
                $timezone = (new \DateTime())->getTimezone();
                if (false === $timezone) {
                    throw new \RuntimeException('Cannot get default timezone.');
                }
            } else {
                $timezone = new \DateTimeZone($value['timezone']);
            }

            return $timezone;
        }

        throw new \InvalidArgumentException('Invalid DateTimeZone constructor arguments. Accepted: `string` or `{"timezone": ?string}`.Given: '.var_export($value, true).'.');
    }

    /**
     * @param string|array{from_string?: bool, date_string?: string, y?: ?int, m?: ?int, d?: ?int, h?: ?int, i?: ?int, s?: ?int, f?: ?float, invert?: ?int, days?: mixed}|object{from_string?: bool, date_string?: string, y?: ?int, m?: ?int, d?: ?int, h?: ?int, i?: ?int, s?: ?int, f?: ?float, invert?: ?int, days?: mixed}|null $value
     */
    public static function DateIntervalConstructor(string|array|object $value = null): \DateInterval
    {
        if (null === $value) {
            throw new \ArgumentCountError('DateInterval::__construct() expects exactly 1 argument, 0 given.');
        }

        if (is_string($value)) {
            try {
                return new \DateInterval($value);
            } catch (\Exception) {
                $interval = \DateInterval::createFromDateString($value);
                if ($interval) {
                    return $interval;
                }
            }
        }

        if (is_object($value)) {
            $value = (array) $value;
        }

        if (isset($value['from_string'])) {
            if ($value['from_string']) {
                $interval = \DateInterval::createFromDateString($value['date_string']);
            } else {
                $interval = new \DateInterval(sprintf(
                    'P%dY%dM%dDT%dH%dM%dS',
                    $value['y'] ?? 0,
                    $value['m'] ?? 0,
                    $value['d'] ?? 0,
                    $value['h'] ?? 0,
                    $value['i'] ?? 0,
                    $value['s'] ?? 0,
                ));
                $interval->f = $value['f'] ?? 0;
                $interval->invert = $value['invert'] ?? 0;
                $interval->days = $value['days'] ?? false;
            }

            if ($interval) {
                return $interval;
            }
        }

        $arg = var_export($value, true);
        throw new \InvalidArgumentException('Invalid DateInterval constructor arguments. Accepted: `P%dY%dM%dDT%dH%dM%dS` or `{"y": ?int, "m": ?int, "d": ?int, "h": ?int, "i": ?int, "s": ?int, "f": ?float, "invert": ?int, "days": mixed, "from_string": false}` or `{"date_string": string, "from_string": true}`. '."Given: {$arg}.");
    }
}
