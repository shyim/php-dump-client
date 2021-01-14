<?php declare(strict_types=1);

namespace PhpDumpClient\Message\Payload;

class ClearPayload extends AbstractPayload
{
    protected string $type = 'clear';
}
