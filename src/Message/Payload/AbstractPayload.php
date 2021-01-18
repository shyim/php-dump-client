<?php declare(strict_types=1);

namespace PhpDumpClient\Message\Payload;

use PhpDumpClient\Struct;

abstract class AbstractPayload extends Struct
{
    /**
     * @var string
     */
    protected $type;
}
