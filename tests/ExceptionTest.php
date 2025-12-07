<?php

/**
 * @author    Joffrey Demetz <joffrey.demetz@gmail.com>
 * @license   MIT License; <https://opensource.org/licenses/MIT>
 */

namespace JDZ\Sitemap\Tests;

use PHPUnit\Framework\TestCase;
use JDZ\Sitemap\Exception;

class ExceptionTest extends TestCase
{
    public function testExceptionIsThrowable()
    {
        $exception = new Exception('Test message');

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
    }

    public function testExceptionCanBeCaught()
    {
        $caught = false;

        try {
            throw new Exception('Test exception');
        } catch (Exception $e) {
            $caught = true;
        }

        $this->assertTrue($caught);
    }

    public function testExceptionCanBeCaughtAsBaseException()
    {
        $caught = false;

        try {
            throw new Exception('Test exception');
        } catch (\Throwable $e) {
            $caught = true;
        }

        $this->assertTrue($caught);
    }
}
