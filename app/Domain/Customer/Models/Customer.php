<?php

namespace App\Domain\Customer\Models;

use App\Domain\Customer\ValueObjects\CustomerId;
use DateTime;

class Customer
{
    private CustomerId $id;
    private string $name;
    private ?string $email;
    private string $phone;
    private ?string $address;
    private DateTime $joinDate;

    public function __construct(
        CustomerId $id,
        string $name,
        string $phone,
        ?string $email = null,
        ?string $address = null,
        ?DateTime $joinDate = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->phone = $phone;
        $this->email = $email;
        $this->address = $address;
        $this->joinDate = $joinDate ?? new DateTime();
    }

    public function getId(): CustomerId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getJoinDate(): DateTime
    {
        return $this->joinDate;
    }

    public function updateInformation(
        string $name,
        string $phone,
        ?string $email = null,
        ?string $address = null
    ): void {
        $this->name = $name;
        $this->phone = $phone;
        $this->email = $email;
        $this->address = $address;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'joinDate' => $this->joinDate->format('Y-m-d')
        ];
    }
}
