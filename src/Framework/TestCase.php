<?php

namespace KPHPUnit\Framework;

/**
 * This class is used as a PHPUnit\Framework\TestCase replacement for KPHP tests.
 * 
 * It implements a compatible public interface to make the compilation/autocomplete
 * work as expected, but it also provides methods expected by the ktest code generator.
 * For example, *withLine methods are the actual methods that are going to be called.
 * The ktest code generator will inject the appropriate $line arguments there.
 */
class TestCase {
    /**
     * @param mixed $condition
     * @param string $message
     */
    public function assertTrue($condition, string $message = '') {
        TestCase::assertTrueWithLine($condition, $message);
    }

    /**
     * @param int $line
     * @param mixed $condition
     * @param string $message
     */
    public function assertTrueWithLine(int $line, $condition, string $message = '') {
        if ($condition === true) {
            TestCase::ok();
        } else {
            TestCase::fail('BOOL', 'true', $condition, $message, $line);
        }
    }

    /**
     * @param mixed $condition
     * @param string $message
     */
    public function assertFalse($condition, string $message = '') {
        TestCase::assertFalseWithLine(0, $condition, $message);
    }

    /**
     * @param int $line
     * @param mixed $condition
     * @param string $message
     */
    public function assertFalseWithLine(int $line, $condition, string $message = '') {
        if ($condition === false) {
            TestCase::ok();
        } else {
            TestCase::fail('BOOL', 'false', $condition, $message, $line);
        }
    }

    /**
     * @param mixed $expected
     * @param mixed $actual
     * @param string $message
     */
    public function assertSame($expected, $actual, string $message = '') {
        TestCase::assertSameWithLine(0, $expected, $actual, $message);
    }

    /**
     * @param int $line
     * @param mixed $expected
     * @param mixed $actual
     * @param string $message
     */
    public function assertSameWithLine(int $line, $expected, $actual, string $message = '') {
        if (TestCase::checkIdentical($expected, $actual)) {
            TestCase::ok();
        } else {
            TestCase::fail('SAME', $expected, $actual, $message, $line);
        }
    }

    /**
     * @param mixed $expected
     * @param mixed $actual
     * @param string $message
     */
    public function assertNotSame($expected, $actual, string $message = '') {
        TestCase::assertNotSameWithLine(0, $expected, $actual, $message);
    }

    /**
     * @param int $line
     * @param mixed $expected
     * @param mixed $actual
     * @param string $message
     */
    public function assertNotSameWithLine(int $line, $expected, $actual, string $message = '') {
        if (!TestCase::checkIdentical($expected, $actual)) {
            TestCase::ok();
        } else {
            TestCase::fail('NOT_SAME', $expected, $actual, $message, $line);
        }
    }

    /**
     * @param mixed $expected
     * @param mixed $actual
     * @param string $message
     */
    public function assertEquals($expected, $actual, string $message = '') {
        TestCase::assertEqualsWithLine(0, $expected, $actual, $message);
    }

    /**
     * @param int $line
     * @param mixed $expected
     * @param mixed $actual
     * @param string $message
     */
    public function assertEqualsWithLine(int $line, $expected, $actual, string $message = '') {
        if (TestCase::checkEquals($expected, $actual)) {
            TestCase::ok();
        } else {
            TestCase::fail('EQUALS', $expected, $actual, $message, $line);
        }
    }

    /**
     * @param mixed $expected
     * @param mixed $actual
     * @param string $message
     */
    public function assertNotEquals($expected, $actual, string $message = '') {
        TestCase::assertNotEqualsWithLine(0, $expected, $actual, $message);
    }

    /**
     * @param int $line
     * @param mixed $expected
     * @param mixed $actual
     * @param string $message
     */
    public function assertNotEqualsWithLine(int $line, $expected, $actual, string $message = '') {
        if (!TestCase::checkEquals($expected, $actual)) {
            TestCase::ok();
        } else {
            TestCase::fail('NOT_EQUALS', $expected, $actual, $message, $line);
        }
    }

    private static function compareAsEqualStrings($expected, $actual): bool {
        return is_string($expected) || is_string($actual);
    }

    private static function compareAsEqualDoubles($expected, $actual): bool {
        return (is_float($expected) || is_float($actual)) && is_numeric($expected) && is_numeric($actual);
    }

    private static function compareAsEqualNumeric($expected, $actual): bool {
        return is_numeric($expected) && is_numeric($actual) &&
                !(is_float($expected) || is_float($actual)) &&
                !(is_string($expected) && is_string($actual));
    }

    private static function stringEqual($expected, $actual): bool {
        return $expected === $actual;
    }

    private static function numericEqual($expected, $actual): bool {
        if (TestCase::isInfinite($actual) && TestCase::isInfinite($expected)) {
            return true;
        }

        if (TestCase::isInfinite($actual) xor TestCase::isInfinite($expected)) {
            return false;
        }
        if (TestCase::isNan($actual) || TestCase::isNan($expected)) {
            return false;
        }

        return $expected == $actual;
    }

    private static function doubleEqual($expected, $actual): bool {
        return abs($expected - $actual) < TestCase::EPSILON;
    }

    /**
     * @param mixed $expected
     * @param mixed $actual
     */
    private static function checkEquals($expected, $actual): bool {
        // TODO: array comparator.
        if (TestCase::compareAsEqualNumeric($expected, $actual)) {
            return TestCase::numericEqual($expected, $actual);
        } else if (TestCase::compareAsEqualDoubles($expected, $actual)) {
            return TestCase::doubleEqual($expected, $actual);
        } else if (TestCase::compareAsEqualStrings($expected, $actual)) {
            return TestCase::stringEqual($expected, $actual);
        }
        return $expected == $actual;
    }

    /**
     * @param mixed $expected
     * @param mixed $actual
     */
    private static function checkIdentical($expected, $actual): bool {
        $float_cmp = is_float($expected) && is_float($actual) &&
                     !is_infinite($expected) && !is_infinite($actual) &&
                     !is_nan($expected) && !is_nan($actual);
        if ($float_cmp) {
            return abs($expected - $actual) < TestCase::EPSILON;
        }
        return $expected === $actual;
    }

    private static function isInfinite($value): bool {
        return is_float($value) && is_infinite($value);
    }

    private static function isNan($value): bool {
        return is_float($value) && is_nan($value);
    }

    private static function ok() {
        echo json_encode(['ASSERT_OK']) . "\n";
    }

    /**
     * @param string $kind
     * @param mixed $expected
     * @param mixed $actual
     * @param string $message
     * @param int $line
     */
    private static function fail(string $kind, $expected, $actual, string $message, int $line) {
        echo json_encode(["ASSERT_{$kind}_FAILED", $expected, $actual, $message, $line]) . "\n";
        throw new AssertionFailedException();
    }

    private const EPSILON = 0.0000000001;
}
