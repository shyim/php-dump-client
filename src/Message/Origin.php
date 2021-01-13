<?php

namespace PhpDumpClient\Message;

use PhpDumpClient\Struct;

class Origin extends Struct
{
    protected string $fileName;
    protected int $lineNumber;

    public function __construct(string $fileName, int $lineNumber)
    {
        $this->fileName = $fileName;
        $this->lineNumber = $lineNumber;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }
}