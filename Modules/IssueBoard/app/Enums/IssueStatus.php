<?php

namespace Modules\IssueBoard\Enums;

enum IssueStatus: string
{
    case New = 'new';       // rot   - neue Aufgabe
    case WithAli = 'with_ali';  // gelb  - Ali bearbeitet
    case WithDev = 'with_dev';  // blau  - Entwicklung bearbeitet
    case Done = 'done';      // gruen - erledigt

    public function label(): string
    {
        return __('issueboard::issueboard.status.'.$this->value);
    }

    /** Hex value used for the column rail and the card dot. */
    public function color(): string
    {
        return match ($this) {
            self::New => '#dc2626',
            self::WithAli => '#d97706',
            self::WithDev => '#2563eb',
            self::Done => '#16a34a',
        };
    }

    /** Tint used behind the badge, so the dot colour stays readable. */
    public function tint(): string
    {
        return match ($this) {
            self::New => '#fef2f2',
            self::WithAli => '#fffbeb',
            self::WithDev => '#eff6ff',
            self::Done => '#f0fdf4',
        };
    }

    public function isOpen(): bool
    {
        return $this !== self::Done;
    }

    /** Statuses that count as "needs attention" in the digest mail. */
    public static function needsAttention(): array
    {
        return [self::New, self::WithAli];
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
