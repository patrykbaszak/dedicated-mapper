<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Service;

interface TypeServiceInterface
{
    /**
     * @example
     * <code>
     * $key = 'value'
     * </code>
     */
    public const PROPERTY = 1;

    /**
     * @example
     * <code>
     * [
     *      'key' => 'value',
     *      'key2' => [
     *          'key3' => 'value3'
     *      ]
     * ]
     * </code>
     */
    public const ARRAY = 2;

    /**
     * @example
     * <code>
     * (object) [
     *      'key' => 'value',
     *      'key2' => [
     *          'key3' => 'value3'
     *      ]
     * ]
     * </code>
     */
    public const OBJECT = 3;

    /**
     * @example
     * <code>
     * new ClassObject(
     *      key: 'value',
     *      key2: new NestedClassObject(
     *          key3: 'value3'
     *      )
     * )
     * </code>
     */
    public const CLASS_OBJECT = 4;

    /**
     * @example
     * <code>
     * # map{.}
     * [
     *      'key' => 'value',
     *      'key2.key3' => 'value3'
     * ]
     * </code>
     */
    public const MAP = 5;

    /**
     * @example
     * <code>
     * # map{.}
     * (object) [
     *      'key' => 'value',
     *      'key2.key3' => 'value3'
     * ]
     * </code>
     */
    public const MAP_OBJECT = 6;

    /**
     * @example
     * <code>
     *
     * /** @var ClassObject[] *\/
     * [
     *  new ClassObject(
     *      key: 'value',
     *      key2: new NestedClassObject(
     *          key3: 'value3'
     *      )
     *  ),
     *  new ClassObject(
     *      key: 'value',
     *      key2: new NestedClassObject(
     *          key3: 'value3'
     *      )
     *  )
     * ]
     * </code>
     */
    public const COLLECTION = 7;

    /**
     * @param class-string|'array'|'object'   $value
     * @param 'array'|'object'|'map{%s}'|null $type
     */
    public function calculateType(string $value, ?string $type): int;
}
