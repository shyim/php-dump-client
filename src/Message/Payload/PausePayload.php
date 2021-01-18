<?php declare(strict_types=1);

namespace PhpDumpClient\Message\Payload;

class PausePayload extends AbstractPayload
{
    /**
     * @var string
     */
    protected $type = 'pause';
}
