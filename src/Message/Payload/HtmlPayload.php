<?php

namespace PhpDumpClient\Message\Payload;

class HtmlPayload extends AbstractPayload
{
    protected string $type = 'html';
    protected string $content;

    public function __construct(string $html)
    {
        $this->content = $html;
    }
}