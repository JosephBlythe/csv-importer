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
            if (!is_string($data[$field])) {
                throw new \InvalidArgumentException(
                    sprintf('Field %s must be a string', $field)
                );
            }
        }

        /** @var array<string, string> $data */
        return new self(
            name: $data['name'],
            surname: $data['surname'],
            email: $data['email']
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
        $this->name = ucfirst(strtolower(trim($name)));
    }

    /**
     * @throws \InvalidArgumentException if surname is empty
     */
    private function setSurname(string $surname): void
    {
        if (trim($surname) === '') {
            throw new \InvalidArgumentException('Surname cannot be empty');
        }
        $this->surname = ucfirst(strtolower(trim($surname)));
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

    /**
     * Get the field mappings for CSV import.
     * Maps model field names to possible CSV header names.
     *
     * @return array<string, array<string>>
     */
    public static function getFieldMappings(): array
    {
        return [
            'name' => ['name', 'first_name', 'firstname'],
            'surname' => ['surname', 'last_name', 'lastname'],
            'email' => ['email', 'email_address', 'emailaddress'],
        ];
    }

    /**
     * Get the required fields for this model.
     *
     * @return array<string>
     */
    public static function getRequiredFields(): array
    {
        return ['name', 'surname', 'email'];
    }
}
