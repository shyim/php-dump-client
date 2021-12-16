<?php declare(strict_types=1);

namespace PhpDumpClient\Extensions;

use Doctrine\DBAL\Query\QueryBuilder;
use PhpDumpClient\Client;

class Doctrine
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function logQueryBuilder($queryBuilder): Client
    {
        if (! $queryBuilder instanceof QueryBuilder) {
            throw new \InvalidArgumentException('Argument one needs to be QueryBuilder');
        }

        $sql = $queryBuilder->getSQL();
        $params = $queryBuilder->getParameters();

        $i = 0;

        if (! \array_key_exists(0, $params) && \array_key_exists(1, $params)) {
            $i = 1;
        }

        $runnableQuery = \preg_replace_callback(
            '/\?|((?<!:):[a-z0-9_]+)/i',
            static function ($matches) use ($params, &$i) {
                $key = \mb_substr($matches[0], 1);

                if (! \array_key_exists($i, $params) && ($key === false || ! \array_key_exists($key, $params))) {
                    return $matches[0];
                }

                $value  = \array_key_exists($i, $params) ? $params[$i] : $params[$key];
                $result = self::escapeFunction($value);
                $i++;

                return $result;
            },
            $sql
        );

        return $this->client->logSql($runnableQuery);
    }

    public static function escapeFunction($parameter): string
    {
        $result = $parameter;

        switch (true) {
            // Check if result is non-unicode string using PCRE_UTF8 modifier
            case \is_string($result) && ! \preg_match('//u', $result):
                $result = '0x' . \mb_strtoupper(\bin2hex($result));
                break;

            case \is_string($result):
                $result = "'" . \addslashes($result) . "'";
                break;

            case \is_array($result):
                foreach ($result as &$value) {
                    $value = static::escapeFunction($value);
                }

                $result = \implode(', ', $result) ?: 'NULL';
                break;

            case \is_object($result):
                $result = \addslashes((string) $result);
                break;

            case $result === null:
                $result = 'NULL';
                break;

            case \is_bool($result):
                $result = $result ? '1' : '0';
                break;
        }

        return (string) $result;
    }
}
