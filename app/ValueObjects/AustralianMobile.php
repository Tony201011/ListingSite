<?php

namespace App\ValueObjects;

use InvalidArgumentException;

class AustralianMobile
{
    /** The normalized 10-digit form: 04XXXXXXXX */
    private string $normalized;

    private function __construct(string $normalized)
    {
        $this->normalized = $normalized;
    }

    /**
     * Parse any common Australian mobile representation into a normalized form.
     *
     * Accepts: "0412 345 678", "0412-345-678", "+61412345678", "61412345678", "0412345678"
     *
     * @throws InvalidArgumentException if the input cannot be parsed into a valid Australian mobile
     */
    public static function fromString(string $input): self
    {
        $digits = preg_replace('/[^\d]/', '', ltrim(trim($input), '+'));

        // +61 / 61 prefix → strip to local
        if (str_starts_with($digits, '61') && strlen($digits) === 11) {
            $digits = '0'.substr($digits, 2);
        }

        if (! preg_match('/^04\d{8}$/', $digits)) {
            throw new InvalidArgumentException(
                'The mobile number must be a valid Australian mobile (04XX XXX XXX).'
            );
        }

        return new self($digits);
    }

    /**
     * Check whether a raw input string can be parsed as a valid Australian mobile.
     */
    public static function isValid(string $input): bool
    {
        try {
            self::fromString($input);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /** E.164 format for Twilio: +614XXXXXXXX */
    public function toE164(): string
    {
        return '+61'.substr($this->normalized, 1);
    }

    /** Local 10-digit format: 04XXXXXXXX */
    public function toLocal(): string
    {
        return $this->normalized;
    }

    /** Masked for display: ******4678 */
    public function toMasked(): string
    {
        $length = strlen($this->normalized);

        return str_repeat('*', $length - 4).substr($this->normalized, -4);
    }

    /**
     * Check equality after normalizing both sides. Useful for dummy-number comparison.
     */
    public function equals(string $other): bool
    {
        try {
            return self::fromString($other)->normalized === $this->normalized;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public function __toString(): string
    {
        return $this->normalized;
    }
}
