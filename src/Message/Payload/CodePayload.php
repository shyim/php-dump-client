<?php declare(strict_types=1);

namespace PhpDumpClient\Message\Payload;

class CodePayload extends AbstractPayload
{
    /**
     * @var string
     */
    protected $type = 'code';

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $language;

    public function __construct(string $code, string $language = 'text')
    {
        $this->code = $code;
        $this->language = $language;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'content' => [
                'value' => $this->code,
                'language' => $this->language
            ]
        ];
    }
}
