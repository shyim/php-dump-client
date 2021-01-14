<?php declare(strict_types=1);

namespace PhpDumpClient;

use Doctrine\SqlFormatter\HtmlHighlighter;
use Doctrine\SqlFormatter\SqlFormatter;
use PhpDumpClient\Extensions\Doctrine;
use PhpDumpClient\Message\Message;
use PhpDumpClient\Message\Payload\ClearPayload;
use PhpDumpClient\Message\Payload\CodePayload;
use PhpDumpClient\Message\Payload\HtmlPayload;
use PhpDumpClient\Message\Payload\PausePayload;
use PhpDumpClient\Message\Payload\TablePayload;
use PhpDumpClient\Message\Timer;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class Client
{
    private string $instanceUrl;

    private array $tags = [];

    public function __construct()
    {
        $this->instanceUrl = $_SERVER['PHP_DUMP_SERVER_URL'] ?? 'http://localhost:9009';
    }

    /**
     * Allows setting a custom server at runtime. Prefer environment variable
     */
    public function setServerUrl(string $url): void
    {
        $this->instanceUrl = $url;
    }

    public function log(... $arguments): self
    {
        $msg = $this->createMessage();

        $cloner = new VarCloner();
        $cloner->setMaxItems(-1);
        $htmlDumper = new HtmlDumper();


        foreach ($arguments as $argument) {
            $data = $htmlDumper->dump($cloner->cloneVar($argument), true);
            $msg->payload(new HtmlPayload($data));
        }

        $this->send($msg);

        return $this;
    }

    public function trace(): self
    {
        $backtraces = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);

        if (\count($backtraces) <= 1) {
            return $this;
        }

        $backtraces = \array_slice($backtraces, 1);

        $table = new TablePayload(['File', 'Function']);
        foreach ($backtraces as $backtrace) {
            if ($backtrace['file'] === __FILE__) {
                continue;
            }

            $function = $backtrace['class'] ? $backtrace['class'] . ':' : '';
            $function .= $backtrace['function'];

            $table->addRow(
                \sprintf('%s:%s', $this->stripPath($backtrace['file']), $backtrace['line']),
                $function
            );
        }

        $msg = $this->createMessage();
        $msg->payload($table);

        $this->send($msg);

        return $this;
    }

    public function clear(): self
    {
        $msg = $this->createMessage();
        $msg->payload(new ClearPayload());

        $this->send($msg);

        return $this;
    }

    public function time(string $title, ?callable $func = null): Timer
    {
        $t = new Timer($title, $this->createMessage(), $this);

        if ($func === null) {
            return $t;
        }

        $func();

        $t->stop();
        return $t;
    }

    public function tag(string ...$tag): self
    {
        $tagInstance = clone $this;
        $tagInstance->tags = [... $tagInstance->tags, ...$tag];

        return $tagInstance;
    }

    public function pause(?string $title = null): self
    {
        $msg = $this->createMessage();

        if ($title) {
            $msg->payload(new HtmlPayload($title));
        }

        $msg->payload(new PausePayload());

        $this->send($msg);

        while ($this->lockExists($msg->getId())) {
            \sleep(1);
        }

        return $this;
    }

    public function logSql(string $sql): self
    {
        $msg = $this->createMessage();

        $highlighterConfig = [
            HtmlHighlighter::HIGHLIGHT_PRE => 'style="color: black; background-color: #e8e8e8;filter: invert(1);"'
        ];

        $msg->payload(new HtmlPayload((new SqlFormatter(new HtmlHighlighter($highlighterConfig)))->format($sql)));

        $this->send($msg);

        return $this;
    }

    public function doctrine(): Doctrine
    {
        return new Doctrine($this);
    }

    public function send(Message $message): void
    {
        $message->tag(...$this->tags);
        $ch = \curl_init($this->instanceUrl . '/client');
        \curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, 'POST');
        \curl_setopt($ch, \CURLOPT_POSTFIELDS, \json_encode($message));
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);

        $headers = [
            'pd-id:' . $message->getId(),
        ];

        if ($message->hasPayload(PausePayload::class)) {
            $headers[] = 'pd-action:pause';
        }

        \curl_setopt($ch, \CURLOPT_HTTPHEADER, $headers);

        \curl_exec($ch);
        \curl_close($ch);
    }

    protected function createMessage(): Message
    {
        $backtrace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 10);

        foreach ($backtrace as $backtrace) {
            if (\strpos($backtrace['file'], __DIR__) === 0) {
                continue;
            }

            return new Message($this->stripPath($backtrace['file']), $backtrace['line']);
        }

        throw new \RuntimeException('Cannot detect entry point');
    }

    private function lockExists(string $id): bool
    {
        $ch = \curl_init($this->instanceUrl . '/is-locked');
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);

        $headers = [
            'pd-id:' . $id,
        ];

        \curl_setopt($ch, \CURLOPT_HTTPHEADER, $headers);

        $resp = \curl_exec($ch);
        \curl_close($ch);

        return $resp === "1";
    }

    private function stripPath(string $path): string
    {
        $currentFolder = \getcwd();

        if (\mb_strpos($path, $currentFolder) === 0) {
            return \mb_substr($path, \mb_strlen($currentFolder) + 1);
        }

        return $path;
    }
}
