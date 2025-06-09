<?php

declare(strict_types=1);

namespace App\Tests\Transformer;

use App\Transformer\UserTransformer;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserTransformer::class)]
final class UserTransformerTest extends TestCase
{
    private UserTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new UserTransformer();
    }

    public function testTransformWithStandardFields(): void
    {
        $data = [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
        ];

        $result = $this->transformer->transform($data);

        $this->assertSame([
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
        ], $result);
    }

    public function testTransformWithAlternativeFieldNames(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email_address' => 'john.doe@example.com',
        ];

        $result = $this->transformer->transform($data);

        $this->assertSame([
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
        ], $result);
    }

    public function testTransformWithMixedCaseFieldNames(): void
    {
        $data = [
            'First_Name' => 'John',
            'SURNAME' => 'Doe',
            'Email_Address' => 'john.doe@example.com',
        ];

        $result = $this->transformer->transform($data);

        $this->assertSame([
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
        ], $result);
    }

    public function testTransformThrowsExceptionForInvalidData(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        /** @var mixed $invalidData */
        $invalidData = 'invalid';
        $this->transformer->transform($invalidData);
    }

    public function testTransformThrowsExceptionForMissingRequiredField(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not find a value for required field: email');

        $this->transformer->transform([
            'name' => 'John',
            'surname' => 'Doe',
        ]);
    }

    public function testTransformTrimsWhitespace(): void
    {
        $data = [
            'name' => ' John ',
            'surname' => ' Doe ',
            'email' => ' john.doe@example.com ',
        ];

        $result = $this->transformer->transform($data);

        $this->assertSame([
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
        ], $result);
    }
}
