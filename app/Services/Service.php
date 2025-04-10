<?php declare(strict_types=1);

namespace App\Services;

class Service
{
    /**
     * Indicates the success of an operation by merging success flag with an optional data array.
     *
     * @param array $data (optional) Additional data to include in the success response.
     * @return array An array containing the success information, with keys:
     *   - 'success' set to true to indicate success.
     *   - Additional keys from the $data array inputted.
     */
    protected function success(array $data = []): array
    {
        return array_merge(['success' => true], $data);
    }

    /**
     * Fails the operation by returning an array indicating failure.
     *
     * @param string $message The message describing the reason for the operation failure.
     * @return array An array containing the failure information, with keys:
     *   - 'success' set to false to indicate failure.
     *   - 'message' containing the provided failure message.
     */
    protected function fail(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }
}