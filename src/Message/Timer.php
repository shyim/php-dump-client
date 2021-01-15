<?php declare(strict_types=1);

namespace PhpDumpClient\Message;

use PhpDumpClient\Client;
use PhpDumpClient\Message\Payload\HtmlPayload;
use PhpDumpClient\Message\Payload\TablePayload;

class Timer
{
    private float $time;

    private int $memoryUsage;

    private int $peakMemoryUsage;

    private TablePayload $table;

    private Client $client;

    private Message $message;

    public function __construct(string $title, Message $message, Client $client)
    {
        $this->time = \microtime(true);
        $this->memoryUsage = \memory_get_usage(true);
        $this->peakMemoryUsage = \memory_get_peak_usage(true);
        $this->table = new TablePayload(['Label', 'Time', 'Total Memory Usage', 'Peak Memory Usage']);
        $this->client = $client;
        $this->message = $message;

        $this->message->payload(new HtmlPayload($title));
        $this->message->payload($this->table);
    }

    public function checkpoint(?string $label = null): self
    {
        return $this->track($label ?? '(Unlabeled)');
    }

    public function stop(): Client
    {
        $this->track('(Stop)');

        $this->client->send($this->message);

        return $this->client;
    }

    protected function track(string $label): self
    {
        $currentMemoryUsage = \memory_get_usage(true);
        $currentPeakMemoryUsage = \memory_get_peak_usage(true);

        $this->table->addRow(
            $label,
            (string) (\microtime(true) - $this->time),
            $this->formatBytes($currentMemoryUsage),
            $this->formatBytes($currentPeakMemoryUsage)
        );

        $this->time = \microtime(true);

        $this->memoryUsage = $currentMemoryUsage;
        $this->peakMemoryUsage = $currentPeakMemoryUsage;

        return $this;
    }

    private function formatBytes($size, $precision = 2): string
    {
        $base = \log($size, 1024);
        $suffixes = ['', 'Kb', 'Mb', 'Gb', 'Tb'];

        return \round(1024 ** ($base - \floor($base)), $precision) . ' ' . $suffixes[\floor($base)];
    }
}
