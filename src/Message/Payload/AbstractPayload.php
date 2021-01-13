<?php

namespace PhpDumpClient\Message\Payload;

use PhpDumpClient\Struct;

abstract class AbstractPayload extends Struct
{
    protected string $type;
}