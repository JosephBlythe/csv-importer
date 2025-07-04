<?php

declare(strict_types=1);

namespace App\Processor;

use App\Database\ConnectionInterface;
use App\Model\User;
use App\Transformer\UserTransformer;

/**
 * Processes user data for database storage.
 */
final class UserProcessor implements Processor
{
    /** @var array<string, string> */
    private array $errors = [];
    
    public function __construct(
        private readonly ConnectionInterface $connection,
        private readonly UserTransformer $transformer
    ) {}

    /**
     * Validate user data without storing in database.
     *
     * @param mixed $data The data to validate
     */
    public function validate(mixed $data): bool
    {
        $this->errors = [];

        if (!is_array($data)) {
            $this->errors['validation'] = 'Data must be an array';
            return false;
        }

        try {
            // Attempt to create user object to validate data
            User::fromArray($data);
            return true;
        } catch (\InvalidArgumentException $e) {
            $this->errors['validation'] = $e->getMessage();
            return false;
        }
    }

    /**
     * Process user data and store in database.
     *
     * @param array<string, string> $data Raw user data
     */
    public function process(mixed $data): bool
    {
        // First validate the data
        if (!$this->validate($data)) {
            return false;
        }

        try {
            $user = User::fromArray($data);
            
            // Check if email already exists
            $pdo = $this->connection->getPdo();
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) FROM users WHERE email = :email'
            );
            $stmt->execute(['email' => $user->getEmail()]);
            
            if ((int) $stmt->fetchColumn() > 0) {
                $this->errors['duplicate'] = sprintf(
                    'Email %s already exists in the database. Each email must be unique.',
                    $user->getEmail()
                );
                return false;
            }

            // Insert user
            $stmt = $pdo->prepare(
                'INSERT INTO users (name, surname, email) VALUES (:name, :surname, :email)'
            );
            
            return $stmt->execute([
                'name' => $user->getName(),
                'surname' => $user->getSurname(),
                'email' => $user->getEmail(),
            ]);

        } catch (\InvalidArgumentException $e) {
            $this->errors['validation'] = sprintf(
                'Data validation error: %s. Please check the input format.',
                $e->getMessage()
            );
            return false;
        } catch (\RuntimeException $e) {
            $this->errors['database'] = sprintf(
                'Database error: %s. Please try again or contact support if the issue persists.',
                $e->getMessage()
            );
            return false;
        }
    }

    /**
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
