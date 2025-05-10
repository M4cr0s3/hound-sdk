<?php

declare(strict_types=1);

namespace Macrose\Hound;

use Macrose\Hound\Attributes\SerializedName;
use ReflectionClass;
use ReflectionProperty;

final class JsonSerializer
{
    public function serialize(object $object): string
    {
        $data = $this->objectToArray($object);
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function objectToArray(object $object): array
    {
        $reflection = new ReflectionClass($object);
        $data = [];

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);

            $serializedName = $this->getSerializedName($property);
            $data[$serializedName] = $this->normalizeValue($value);
        }

        return $data;
    }

    private function getSerializedName(ReflectionProperty $property): string
    {
        $attributes = $property->getAttributes(SerializedName::class);
        return $attributes ? $attributes[0]->newInstance()->name : $property->getName();
    }

    private function normalizeValue(mixed $value): mixed
    {
        if (is_object($value)) {
            return $this->objectToArray($value);
        }

        if (is_array($value)) {
            return array_map([$this, 'normalizeValue'], $value);
        }

        return $value;
    }
}
