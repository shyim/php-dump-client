<?php declare(strict_types=1);

namespace PhpDumpClient\Message\Payload;

class TablePayload extends AbstractPayload
{
    /**
     * @var string
     */
    protected $type = 'table';

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $rows = [];

    public function __construct(array $headers = [], array $rows = [])
    {
        $this->headers = $headers;
        $this->rows = $rows;
    }

    public function setHeaders(array $headers = []): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function addHeader(string ...$header): self
    {
        $this->headers = \array_merge($this->headers, $header);

        return $this;
    }

    public function setRows(array $rows): self
    {
        $this->rows = $rows;

        return $this;
    }

    public function addRow(string ...$row): self
    {
        $this->rows[] = $row;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'content' => [
                'headers' => $this->headers,
                'rows' => $this->rows
            ]
        ];
    }
}
