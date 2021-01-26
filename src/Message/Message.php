<?php declare(strict_types=1);

namespace PhpDumpClient\Message;

use PhpDumpClient\Message\Payload\AbstractPayload;
use PhpDumpClient\Struct;
use PhpDumpClient\Uuid;

class Message extends Struct
{
    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var Origin
     */
    protected $origin;

    /**
     * @var float
     */
    protected $time;

    /**
     * @var string[]
     */
    protected $tags = [];

    /**
     * @var AbstractPayload[]
     */
    protected $payloads = [];

    public function __construct(string $fileName, int $lineNumber, ?string $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::randomHex();
        $this->time = \microtime(true);
        $this->origin = new Origin($fileName, $lineNumber);
    }

    public function tag(string ...$tag): self
    {
        $this->tags = \array_merge($this->tags, $tag);

        return $this;
    }

    public function payload(AbstractPayload $payload): self
    {
        $this->payloads[] = $payload;

        return $this;
    }

    public function hasPayload(string $payloadClass): bool
    {
        foreach ($this->payloads as $payload) {
            if ($payload instanceof $payloadClass) {
                return true;
            }
        }

        return false;
    }

    public function getId(): string
    {
        return $this->uuid;
    }
}
