<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Support\FormValidator;
use PHPUnit\Framework\TestCase;

final class FormValidatorTest extends TestCase
{
    public function testRequiredRejectsEmptyAndWhitespace(): void
    {
        $validator = (new FormValidator(['name' => '   ']))
            ->required('name', 'validation.required');

        self::assertTrue($validator->hasErrors());
        self::assertSame(['validation.required'], $validator->errors()['name']);
    }

    public function testRequiredAcceptsZeroAndTrimsValues(): void
    {
        $validator = (new FormValidator(['count' => ' 0 ', 'name' => '  Ala  ']))
            ->required('count', 'validation.required');

        self::assertFalse($validator->hasErrors());
        self::assertSame('Ala', $validator->value('name'));
    }

    public function testEmailSkipsEmptyAndRejectsInvalidValues(): void
    {
        $empty = (new FormValidator(['email' => '']))->email('email', 'validation.email');
        self::assertFalse($empty->hasErrors());

        $valid = (new FormValidator(['email' => 'user@example.test']))->email('email', 'validation.email');
        self::assertFalse($valid->hasErrors());

        $invalid = (new FormValidator(['email' => 'not-an-email']))->email('email', 'validation.email');
        self::assertSame(['validation.email'], $invalid->errors()['email']);
    }

    public function testLengthRulesCountCharacters(): void
    {
        $tooLong = (new FormValidator(['name' => 'ąćęłńóśźż']))->maxLength('name', 4, 'validation.max');
        self::assertTrue($tooLong->hasErrors());

        $tooShort = (new FormValidator(['name' => 'ąć']))->minLength('name', 3, 'validation.min');
        self::assertTrue($tooShort->hasErrors());

        $valid = (new FormValidator(['name' => 'ąćę']))->minLength('name', 3, 'validation.min');
        self::assertFalse($valid->hasErrors());
    }

    public function testDateAcceptsOnlyRealCalendarDates(): void
    {
        $valid = (new FormValidator(['date' => '2026-11-20']))->date('date', 'validation.date');
        self::assertFalse($valid->hasErrors());

        $impossible = (new FormValidator(['date' => '2026-02-30']))->date('date', 'validation.date');
        self::assertTrue($impossible->hasErrors());

        $wrongFormat = (new FormValidator(['date' => '20.11.2026']))->date('date', 'validation.date');
        self::assertTrue($wrongFormat->hasErrors());
    }

    public function testDateNotBeforeRejectsOnlyPastDates(): void
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        $past = (new FormValidator(['date' => $yesterday]))->dateNotBefore('date', $today, 'validation.past');
        self::assertSame(['validation.past'], $past->errors()['date']);

        self::assertFalse((new FormValidator(['date' => $today]))->dateNotBefore('date', $today, 'validation.past')->hasErrors());
        self::assertFalse((new FormValidator(['date' => $tomorrow]))->dateNotBefore('date', $today, 'validation.past')->hasErrors());
        self::assertFalse((new FormValidator(['date' => '']))->dateNotBefore('date', $today, 'validation.past')->hasErrors());
        self::assertFalse((new FormValidator(['date' => 'nope']))->dateNotBefore('date', $today, 'validation.past')->hasErrors());
    }

    public function testTimeAcceptsOnly24HourFormat(): void
    {
        $valid = (new FormValidator(['time' => '09:30']))->time('time', 'validation.time');
        self::assertFalse($valid->hasErrors());

        foreach (['9:30', '24:00', '12:60', '09.30'] as $value) {
            $invalid = (new FormValidator(['time' => $value]))->time('time', 'validation.time');
            self::assertTrue($invalid->hasErrors(), $value);
        }
    }

    public function testAfterTimeComparesValidTimes(): void
    {
        $valid = (new FormValidator(['start' => '09:00', 'end' => '10:30']))
            ->afterTime('start', 'end', 'validation.after');
        self::assertFalse($valid->hasErrors());

        $equal = (new FormValidator(['start' => '09:00', 'end' => '09:00']))
            ->afterTime('start', 'end', 'validation.after');
        self::assertSame(['validation.after'], $equal->errors()['end']);

        $before = (new FormValidator(['start' => '10:00', 'end' => '09:00']))
            ->afterTime('start', 'end', 'validation.after');
        self::assertTrue($before->hasErrors());
    }

    public function testAfterTimeIgnoresMissingOrInvalidTimes(): void
    {
        $empty = (new FormValidator(['start' => '', 'end' => '']))->afterTime('start', 'end', 'validation.after');
        self::assertFalse($empty->hasErrors());

        $invalid = (new FormValidator(['start' => 'x', 'end' => 'y']))->afterTime('start', 'end', 'validation.after');
        self::assertFalse($invalid->hasErrors());
    }

    public function testErrorsCanBeReadPerField(): void
    {
        $validator = (new FormValidator(['name' => 'ab']))
            ->minLength('name', 3, 'validation.min')
            ->maxLength('name', 1, 'validation.max');

        self::assertSame('validation.min', $validator->firstError('name'));
        self::assertNull($validator->firstError('missing'));
        self::assertCount(2, $validator->errors()['name']);
    }

    public function testNonStringValuesAreTreatedAsEmpty(): void
    {
        $validator = (new FormValidator(['name' => ['array']]))
            ->required('name', 'validation.required');

        self::assertSame('', $validator->value('name'));
        self::assertTrue($validator->hasErrors());
    }
}
