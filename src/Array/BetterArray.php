<?php

namespace App\Array;

class BetterArray
{
    public function __construct(
        private array $data = []
    ) {
    }

    public function contains(mixed $search): bool
    {
        return in_array($search, $this->data);
    }

    public function add(mixed $item): BetterArray
    {
        $this->data[] = $item;

        return $this;
    }

    public function remove(mixed $item): BetterArray
    {
        $this->data[$item] = null;

        return $this;
    }

    public function addKeyValue(mixed $key, mixed $value): BetterArray
    {
        $this->data[$key] = $value;

        return $this;
    }

    public static function fromArray(array $data): BetterArray
    {
        return new BetterArray($data);
    }
}
