<?php

namespace App\Enums\Contracts;

enum ContractType :string
{
    case SaleCollaboration = 'sale_collaboration';
    case SaleExclusive = 'sale_exclusive';
    case SaleUnique = 'sale_unique';
    case RentCustomer = 'rent_customer';
    case RentOwner = 'rent_owner';

    public function label(): string
    {
        return match ($this) {
            self::SaleCollaboration => 'Sale (Collaboration)',
            self::SaleExclusive     => 'Sale (Exclusive)',
            self::SaleUnique        => 'Sale (Unique)',
            self::RentCustomer      => 'Rent (Customer)',
            self::RentOwner         => 'Rent (Owner)',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}
