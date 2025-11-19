<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Structurizr;

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Structurizr\CliWrapperInterface;

class CliWrapperInterfaceTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(CliWrapperInterface::class));
    }

    public function testInterfaceDefinesValidateMethod(): void
    {
        $reflection = new \ReflectionClass(CliWrapperInterface::class);
        $this->assertTrue($reflection->hasMethod('validate'));
    }

    public function testInterfaceDefinesExportMethod(): void
    {
        $reflection = new \ReflectionClass(CliWrapperInterface::class);
        $this->assertTrue($reflection->hasMethod('export'));
    }

    public function testInterfaceDefinesPushMethod(): void
    {
        $reflection = new \ReflectionClass(CliWrapperInterface::class);
        $this->assertTrue($reflection->hasMethod('push'));
    }

    public function testInterfaceDefinesPullMethod(): void
    {
        $reflection = new \ReflectionClass(CliWrapperInterface::class);
        $this->assertTrue($reflection->hasMethod('pull'));
    }

    public function testInterfaceDefinesGetVersionMethod(): void
    {
        $reflection = new \ReflectionClass(CliWrapperInterface::class);
        $this->assertTrue($reflection->hasMethod('getVersion'));
    }
}
