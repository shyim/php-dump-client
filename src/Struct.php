<?php

namespace PhpDumpClient;


class Struct implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}