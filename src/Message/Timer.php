<?php

namespace PhpDumpClient\Message;

use PhpDumpClient\Client;
use PhpDumpClient\Message\Payload\HtmlPayload;
use PhpDumpClient\Message\Payload\TablePayload;

class Timer
{
    private int $time;
    private int $memoryUsage;
    private int $peakMemoryUsage;
    private TablePayload $table;
    private Client $client;
    private Message $message;
    private string $title;

    public function __construct(string $title, Message $message, Client $client)
    {
        $this->time = microtime(true);
        $this->memoryUsage = memory_get_usage(true);
        $this->peakMemoryUsage = memory_get_peak_usage(true);
        $this->table = new TablePayload(['Time', 'Total Memory Usage', 'Peak Memory Usage']);
        $this->client = $client;
        $this->message = $message;
        $this->title = $title;

        $this->message->payload(new HtmlPayload($title));
        $this->message->payload($this->table);
    }

    public function checkpoint(): self
    {
        return $this->track();
    }

    public function stop(): Client
    {
        $this->track();

        $this->client->send($this->message);

        return $this->client;
    }

    protected function track(): self
    {
        $currentMemoryUsage = memory_get_usage(true);
        $currentPeakMemoryUsage = memory_get_peak_usage(true);

        $this->table->addRow(
            microtime(true) - $this->time,
            $this->formatBytes($currentMemoryUsage),
            $this->formatBytes($currentPeakMemoryUsage)
        );

        $this->time = microtime(true);

        $this->memoryUsage = $currentMemoryUsage;
        $this->peakMemoryUsage = $currentPeakMemoryUsage;

        return $this;
    }

    private function formatBytes($size, $precision = 2): string
    {
        $base = log($size, 1024);
        $suffixes = ['', 'Kb', 'Mb', 'Gb', 'Tb'];

        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    }
}
