<?php

declare(strict_types=1);

namespace App\Model;

/**
 * User model representing a user in the system.
 */
final class User
{
    private string $name;
    private string $surname;
    private string $email;

    /**
     * @throws \InvalidArgumentException if the email is invalid or name/surname are empty
     */
    public function __construct(
        string $name,
        string $surname,
        string $email
    ) {
        $this->setName($name);
        $this->setSurname($surname);
        $this->setEmail($email);
    }

    /**
     * Creates a User instance from an array of data.
     *
     * @param array<string, mixed> $data
     * @throws \InvalidArgumentException if required fields are missing or invalid
     */
    public static function fromArray(array $data): self
    {
        foreach (['name', 'surname', 'email'] as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException(
                    sprintf('Missing required field: %s', $field)
                );
            }
        }

        return new self(
            name: (string) $data['name'],
            surname: (string) $data['surname'],
            email: (string) $data['email']
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @throws \InvalidArgumentException if name is empty
     */
    private function setName(string $name): void
    {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('Name cannot be empty');
        }
        $this->name = trim($name);
    }

    /**
     * @throws \InvalidArgumentException if surname is empty
     */
    private function setSurname(string $surname): void
    {
        if (trim($surname) === '') {
            throw new \InvalidArgumentException('Surname cannot be empty');
        }
        $this->surname = trim($surname);
    }

    /**
     * @throws \InvalidArgumentException if email is invalid
     */
    private function setEmail(string $email): void
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
        $this->email = $email;
    }
}
