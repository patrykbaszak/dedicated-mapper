<?php

return function (mixed $data): mixed {
    $mapped = (object) [];
    $mapped->text = $data->text;
    $mapped->number = $data->number;
    $mapped->bool = $data->bool;
    $mapped->nullable = $data->nullable ?? null ?? null;
    $mapped->nullableInt = $data->nullableInt ?? null ?? null;
    $mapped->nullableBool = $data->nullableBool ?? null ?? null;
    $mapped->nullableFloat = $data->nullableFloat ?? null ?? null;
    $mapped->nullableArray = $data->nullableArray ?? null ?? null;
    $mapped->nullableObject = $data->nullableObject ?? null ?? null;
    $mapped->nullableDateTime = $data->nullableDateTime ?? null ?? null;
    $mapped->dateTime = $data->dateTime;
    $mapped->simpleDataSet = (object) [];
    if (($simpleDataSet = $data->someTargetedProperty) instanceof \PBaszak\MessengerMapperBundle\Tests\Assets\SimpleDataSet) {
        $mapped->simpleDataSet->text = $simpleDataSet->text ?? $simpleDataSet->text;
        $mapped->simpleDataSet->number = $simpleDataSet->getNumber();
        $mapped->simpleDataSet->bool = $simpleDataSet->bool ?? $simpleDataSet->bool;
        $mapped->simpleDataSet->nullable = $simpleDataSet->getNullable() ?? null ?? null;
        $mapped->simpleDataSet->nullableInt = $simpleDataSet->getNullableInt() ?? null ?? null;
        $mapped->simpleDataSet->nullableBool = $simpleDataSet->getNullableBool() ?? null ?? null;
        $mapped->simpleDataSet->nullableFloat = $simpleDataSet->getNullableFloat() ?? null ?? null;
        $mapped->simpleDataSet->nullableArray = $simpleDataSet->getNullableArray() ?? null ?? null;
        $mapped->simpleDataSet->nullableObject = $simpleDataSet->getNullableObject() ?? null ?? null;
        $mapped->simpleDataSet->nullableDateTime = $simpleDataSet->getNullableDateTime() ?? null ?? null;
        $mapped->simpleDataSet->dateTime = $simpleDataSet->dateTime ?? $simpleDataSet->dateTime;
        $mapped->simpleDataSet->targetProperty = $simpleDataSet->someTargetedProperty ?? $simpleDataSet->targetProperty;
    } else {
        $mapped->simpleDataSet->text = $data->someTargetedProperty->text;
        $mapped->simpleDataSet->number = $data->someTargetedProperty->number;
        $mapped->simpleDataSet->bool = $data->someTargetedProperty->bool;
        $mapped->simpleDataSet->nullable = $data->someTargetedProperty->nullable ?? null ?? null;
        $mapped->simpleDataSet->nullableInt = $data->someTargetedProperty->nullableInt ?? null ?? null;
        $mapped->simpleDataSet->nullableBool = $data->someTargetedProperty->nullableBool ?? null ?? null;
        $mapped->simpleDataSet->nullableFloat = $data->someTargetedProperty->nullableFloat ?? null ?? null;
        $mapped->simpleDataSet->nullableArray = $data->someTargetedProperty->nullableArray ?? null ?? null;
        $mapped->simpleDataSet->nullableObject = $data->someTargetedProperty->nullableObject ?? null ?? null;
        $mapped->simpleDataSet->nullableDateTime = $data->someTargetedProperty->nullableDateTime ?? null ?? null;
        $mapped->simpleDataSet->dateTime = $data->someTargetedProperty->dateTime;
        $mapped->simpleDataSet->targetProperty = $data->someTargetedProperty->someTargetedProperty;
    }

    return $mapped;
};
