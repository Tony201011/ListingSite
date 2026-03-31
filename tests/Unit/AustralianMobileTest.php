<?php

namespace Tests\Unit;

use App\ValueObjects\AustralianMobile;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AustralianMobileTest extends TestCase
{
    // ---------------------------------------------------------------
    // Valid inputs → local format
    // ---------------------------------------------------------------

    public function test_parses_standard_local_format(): void
    {
        $phone = AustralianMobile::fromString('0412345678');
        $this->assertSame('0412345678', $phone->toLocal());
    }

    public function test_parses_local_with_spaces(): void
    {
        $phone = AustralianMobile::fromString('0412 345 678');
        $this->assertSame('0412345678', $phone->toLocal());
    }

    public function test_parses_local_with_dashes(): void
    {
        $phone = AustralianMobile::fromString('0412-345-678');
        $this->assertSame('0412345678', $phone->toLocal());
    }

    public function test_parses_e164_format(): void
    {
        $phone = AustralianMobile::fromString('+61412345678');
        $this->assertSame('0412345678', $phone->toLocal());
    }

    public function test_parses_international_without_plus(): void
    {
        $phone = AustralianMobile::fromString('61412345678');
        $this->assertSame('0412345678', $phone->toLocal());
    }

    public function test_parses_e164_with_spaces(): void
    {
        $phone = AustralianMobile::fromString('+61 412 345 678');
        $this->assertSame('0412345678', $phone->toLocal());
    }

    public function test_parses_with_parentheses_and_mixed_separators(): void
    {
        $phone = AustralianMobile::fromString('(04) 1234-5678');
        $this->assertSame('0412345678', $phone->toLocal());
    }

    // ---------------------------------------------------------------
    // E.164 output
    // ---------------------------------------------------------------

    public function test_to_e164_from_local(): void
    {
        $phone = AustralianMobile::fromString('0412345678');
        $this->assertSame('+61412345678', $phone->toE164());
    }

    public function test_to_e164_from_international(): void
    {
        $phone = AustralianMobile::fromString('+61412345678');
        $this->assertSame('+61412345678', $phone->toE164());
    }

    // ---------------------------------------------------------------
    // Masking
    // ---------------------------------------------------------------

    public function test_to_masked_hides_all_but_last_four(): void
    {
        $phone = AustralianMobile::fromString('0412345678');
        $this->assertSame('******5678', $phone->toMasked());
    }

    // ---------------------------------------------------------------
    // Equality / dummy comparison
    // ---------------------------------------------------------------

    public function test_equals_matches_same_number_different_formats(): void
    {
        $phone = AustralianMobile::fromString('0400000000');

        $this->assertTrue($phone->equals('0400000000'));
        $this->assertTrue($phone->equals('+61400000000'));
        $this->assertTrue($phone->equals('61400000000'));
        $this->assertTrue($phone->equals('0400 000 000'));
    }

    public function test_equals_returns_false_for_different_number(): void
    {
        $phone = AustralianMobile::fromString('0400000000');

        $this->assertFalse($phone->equals('0411111111'));
    }

    public function test_equals_returns_false_for_invalid_input(): void
    {
        $phone = AustralianMobile::fromString('0400000000');

        $this->assertFalse($phone->equals('not-a-phone'));
    }

    // ---------------------------------------------------------------
    // Validation helper
    // ---------------------------------------------------------------

    public function test_is_valid_returns_true_for_valid_numbers(): void
    {
        $this->assertTrue(AustralianMobile::isValid('0412345678'));
        $this->assertTrue(AustralianMobile::isValid('+61412345678'));
    }

    public function test_is_valid_returns_false_for_invalid_numbers(): void
    {
        $this->assertFalse(AustralianMobile::isValid('1234567890'));
        $this->assertFalse(AustralianMobile::isValid(''));
        $this->assertFalse(AustralianMobile::isValid('not a number'));
    }

    // ---------------------------------------------------------------
    // Invalid inputs → exception
    // ---------------------------------------------------------------

    public function test_rejects_non_mobile_australian_number(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AustralianMobile::fromString('0212345678'); // landline
    }

    public function test_rejects_too_short(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AustralianMobile::fromString('041234567');
    }

    public function test_rejects_too_long(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AustralianMobile::fromString('04123456789');
    }

    public function test_rejects_us_number(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AustralianMobile::fromString('+14155551234');
    }

    public function test_rejects_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AustralianMobile::fromString('');
    }

    public function test_rejects_letters_only(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AustralianMobile::fromString('abcdefghij');
    }

    // ---------------------------------------------------------------
    // __toString
    // ---------------------------------------------------------------

    public function test_to_string_returns_local_format(): void
    {
        $phone = AustralianMobile::fromString('+61412345678');
        $this->assertSame('0412345678', (string) $phone);
    }
}
