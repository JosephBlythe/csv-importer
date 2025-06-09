<?php

declare(strict_types=1);

namespace App\Tests\Processor;

use App\Processor\Processor;
use PHPUnit\Framework\TestCase;
use ReflectionNamedType;

/**
 * This is a test case that ensures the contract of the Processor interface.
 * Any class implementing the Processor interface should follow these test cases.
 */
abstract class ProcessorContractTest extends TestCase
{
    protected Processor $processor;

    /**
     * Implement this in concrete test classes to provide the processor instance.
     */
    abstract protected function createProcessor(): Processor;

    protected function setUp(): void
    {
        $this->processor = $this->createProcessor();
    }

    public function testProcessorImplementsInterface(): void
    {
        $this->assertInstanceOf(Processor::class, $this->processor);
    }

    public function testProcessorHasProcessMethod(): void
    {
        $reflectionClass = new \ReflectionClass($this->processor);
        $this->assertTrue($reflectionClass->hasMethod('process'));
        
        $processMethod = $reflectionClass->getMethod('process');
        $this->assertTrue($processMethod->isPublic());
        
        $returnType = $processMethod->getReturnType();
        $this->assertNotNull($returnType, 'process() method must have a return type');
        $this->assertInstanceOf(ReflectionNamedType::class, $returnType);
        $this->assertSame('bool', $returnType->getName());
    }

    public function testProcessorHasGetErrorsMethod(): void
    {
        $reflectionClass = new \ReflectionClass($this->processor);
        $this->assertTrue($reflectionClass->hasMethod('getErrors'));
        
        $getErrorsMethod = $reflectionClass->getMethod('getErrors');
        $this->assertTrue($getErrorsMethod->isPublic());
        
        $returnType = $getErrorsMethod->getReturnType();
        $this->assertNotNull($returnType, 'getErrors() method must have a return type');
        $this->assertInstanceOf(ReflectionNamedType::class, $returnType);
        $this->assertSame('array', $returnType->getName());
    }
}
