<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Model\User;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
final class UserTest extends TestCase
{
    public function testUserCanBeCreatedWithValidData(): void
    {
        $user = new User(
            name: 'John Doe',
            surname: 'Smith',
            email: 'john.doe@example.com'
        );

        $this->assertSame('John Doe', $user->getName());
        $this->assertSame('Smith', $user->getSurname());
        $this->assertSame('john.doe@example.com', $user->getEmail());
    }

    public function testEmailMustBeValid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        new User(
            name: 'John',
            surname: 'Doe',
            email: 'invalid-email'
        );
    }

    public function testNameAndSurnameCannotBeEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Name cannot be empty');

        new User(
            name: '',
            surname: 'Doe',
            email: 'john.doe@example.com'
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Surname cannot be empty');

        new User(
            name: 'John',
            surname: '',
            email: 'john.doe@example.com'
        );
    }

    public function testEmailIsNormalizedToLowerCase(): void
    {
        $user = new User(
            name: 'John',
            surname: 'Doe',
            email: 'John.Doe@Example.com'
        );

        $this->assertSame('john.doe@example.com', $user->getEmail());
    }

    public function testUserCanBeCreatedFromArray(): void
    {
        $data = [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com'
        ];

        $user = User::fromArray($data);

        $this->assertSame('John', $user->getName());
        $this->assertSame('Doe', $user->getSurname());
        $this->assertSame('john.doe@example.com', $user->getEmail());
    }

    public function testFromArrayThrowsExceptionOnMissingData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field: email');

        User::fromArray([
            'name' => 'John',
            'surname' => 'Doe'
        ]);
    }
}
