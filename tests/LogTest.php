<?php

declare(strict_types=1);

namespace myth21\viewcontroller\tests;

use ErrorException;
use myth21\viewcontroller\WebApp;
use PHPUnit\Framework\TestCase;
use Throwable;

use function unlink;

use const DIRECTORY_SEPARATOR;

class LogTest extends TestCase
{
    public function testWriteLog(): void
    {
        $exceptionMessage = 'An Error Exception';
        $pathToLog = __DIR__ . DIRECTORY_SEPARATOR . '.throwable_log';

        $app = new WebApp(['callableLogger' => function (Throwable $e) use ($pathToLog) {
            $message = $e->getMessage();
            file_put_contents($pathToLog, $message);
        }]);

        $errorException = new ErrorException($exceptionMessage);
        $app->writeLog($errorException);

        $this->assertStringEqualsFile($pathToLog, $exceptionMessage);

        unlink($pathToLog);
    }
}