<?php

namespace App\Infrastructure\Customer\Mappers;

use App\Domain\Customer\Models\Customer as CustomerDomain;
use App\Domain\Customer\ValueObjects\CustomerId;
use App\Models\Customer as CustomerEloquent;
use DateTime;

class CustomerMapper
{
    /**
     * Map from Eloquent model to Domain model
     */
    public function toDomain(CustomerEloquent $model): CustomerDomain
    {
        return new CustomerDomain(
            new CustomerId($model->id),
            $model->name,
            $model->phone,
            $model->email,
            $model->address,
            new DateTime($model->created_at)
        );
    }

    /**
     * Map from Domain model to array for persistence
     */
    public function toPersistence(CustomerDomain $customer): array
    {
        return [
            'name' => $customer->getName(),
            'phone' => $customer->getPhone(),
            'email' => $customer->getEmail(),
            'address' => $customer->getAddress()
        ];
    }
}
