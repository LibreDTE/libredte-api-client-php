<?php

declare(strict_types=1);

namespace libredte\helpers;

use libredte\api_client\ApiException;
use PHPUnit\Framework\SkippedTestSuiteError;

trait FunctionHelpers
{
    protected static $client;

    protected static function requireEnv(string $str_var): void
    {
        $value =
            $_ENV[$str_var]
            ?? $_SERVER[$str_var]
            ?? getenv($str_var);

        if ($value == false || $value == null || $value == '') {
            throw new SkippedTestSuiteError(
                sprintf($str_var . ' no está definido.')
            );
        }
    }

    protected function handleApiException(ApiException $e): void
    {
        $code = (int) $e->getCode();
        $message = sprintf(
            '[ApiException %d] %s',
            $code,
            $e->getMessage()
        );
        if ($code >= 400 && $code < 500 && env('ADD_SKIPPED', false)) {
            $this->markTestSkipped($message);
        }
        $this->fail($message);

    }
}
