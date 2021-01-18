<?php declare(strict_types=1);

namespace PhpDumpClient\Message\Payload;

class HtmlPayload extends AbstractPayload
{
    /**
     * @var string
     */
    protected $type = 'html';

    /**
     * @var string
     */
    protected $content;

    public function __construct(string $html)
    {
        $this->content = $html;
    }
}
