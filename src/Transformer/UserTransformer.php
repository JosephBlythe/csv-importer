<?php

declare(strict_types=1);

namespace App\Transformer;

/**
 * Transforms raw user data into a format suitable for the User model.
 */
final class UserTransformer implements Transformer
{
    /**
     * Transform raw user data into an array suitable for User::fromArray().
     *
     * @param array<string, string> $data Raw user data
     * @return array<string, string> Transformed user data
     * @throws \InvalidArgumentException If the data is invalid or missing required fields
     */
    public function transform(mixed $data): array
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Data must be an array');
        }

        // Map CSV headers to user fields
        $mapping = [
            'name' => ['name', 'first_name', 'firstname'],
            'surname' => ['surname', 'last_name', 'lastname'],
            'email' => ['email', 'email_address', 'emailaddress'],
        ];

        $result = [];

        foreach ($mapping as $field => $possibleKeys) {
            $value = $this->findValue($data, $possibleKeys);
            if ($value === null) {
                throw new \InvalidArgumentException(
                    sprintf('Could not find a value for required field: %s', $field)
                );
            }
            $result[$field] = $value;
        }

        return $result;
    }

    /**
     * Find a value in the data array using possible key names.
     *
     * @param array<string, mixed> $data
     * @param array<string> $possibleKeys
     */
    private function findValue(array $data, array $possibleKeys): ?string
    {
        // Case-insensitive key matching
        $lowerData = array_change_key_case($data, CASE_LOWER);
        
        foreach ($possibleKeys as $key) {
            $lowerKey = strtolower($key);
            if (isset($lowerData[$lowerKey])) {
                $value = $lowerData[$lowerKey];
                if (is_string($value) || is_numeric($value)) {
                    return trim((string)$value);
                }
            }
        }

        return null;
    }
}
