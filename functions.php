<?php declare(strict_types=1);

use PhpDumpClient\Client;

if (!\function_exists('pd')) {
    function pd(): Client
    {
        static $client;

        if ($client === null) {
            $client = new Client();
        }

        // To not overwrite the last message again and again
        $client->setLastMessageId();
        return $client;
    }
}
