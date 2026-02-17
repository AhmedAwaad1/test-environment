<?php

namespace App\Traits;

trait CanCastBooleans
{
    /**
     * Cast a value to a boolean.
     * Handles "1", "0", "true", "false" strings.
     *
     * @param mixed $value
     * @return bool
     */
    protected function castToBoolean($value): bool
    {
        return $value == '1' || $value === 'true' || $value === true || $value === 1;
    }

    /**
     * Cast multiple keys in an array to booleans if they exist.
     *
     * @param array $data
     * @param array $keys
     * @return void
     */
    protected function castBooleansInArray(array &$data, array $keys): void
    {
        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $data[$key] = $this->castToBoolean($data[$key]);
            }
        }
    }
}
