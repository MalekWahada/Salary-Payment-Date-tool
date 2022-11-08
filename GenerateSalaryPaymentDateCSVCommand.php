<?php

execute($argv[1] ?? (date('Y') . " payments dates"));

function execute(string $fileName): void
{
    $date = new DateTimeImmutable('last day of this month');
    $currentYear = $date->format('Y');
    $nextYear = (string)++$currentYear;

    $fp = fopen("{$fileName}.csv", 'w');
    fputcsv(
        $fp,
        [
            'MONTH',
            'SALARY PAYMENT DATE',
            'BONUS PAYMENT DATE',
        ]
    );
    $i = 0;
    do {
        fputcsv(
            $fp,
            [
                $date->format('F'),
                getSalaryPaymentDate($date),
                getBonusPaymentDate($date),
            ]
        );
        $i++;
        $date = $date->modify('last day of 1 month');
    } while ($date->format('Y') < $nextYear);

    fclose($fp);
}

function getSalaryPaymentDate(DateTimeImmutable $date): string
{
    $weekDay = $date->format('D');

    if (isWorkingDay($weekDay)) {
        return $date->format('Y-m-d');
    }

    $amount = $weekDay === 'Sat' ? 1 : 2;

    return $date->modify("-{$amount} days")->format('Y-m-d');
}

function getBonusPaymentDate(DateTimeImmutable $date): string
{
    // Bonus payment date is usually the 15th of the next month
    $month = $date->format('m');
    $year = $date->format('Y');

    $newDate = DateTime::createFromImmutable($date)
        ->setDate(
            $month === '12' ? (++$year) : $year,
            $month === '12' ? 1 : (++$month),
            15
        );

    $weekDay = $date->format('D');

    if (isWorkingDay($weekDay)) {
        return $newDate->format('Y-m-d');
    }

    $amount = $weekDay === 'Sat' ? 3 : 2;

    return $newDate->modify("+{$amount} days")->format('Y-m-d');
}

function isWorkingDay(string $weekDay): bool
{
    return !in_array(
        $weekDay,
        [
            'Sat',
            'Sun',
        ],
        true
    );
}

die();
