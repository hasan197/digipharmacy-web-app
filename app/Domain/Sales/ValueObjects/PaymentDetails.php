<?php

namespace App\Domain\Sales\ValueObjects;

class PaymentDetails
{
    private string $method;
    private bool $isValid;
    private ?string $cardLast4;
    private ?string $cardType;
    private ?string $approvalCode;
    private ?string $transactionId;
    private float $amountPaid;
    private float $change;
    private array $otherData;

    public function __construct(
        string $method, 
        bool $isValid, 
        ?string $cardLast4 = null,
        ?string $cardType = null,
        ?string $approvalCode = null,
        ?string $transactionId = null,
        float $amountPaid = 0,
        float $change = 0,
        array $otherData = []
    ) {
        $this->method = $method;
        $this->isValid = $isValid;
        $this->cardLast4 = $cardLast4;
        $this->cardType = $cardType;
        $this->approvalCode = $approvalCode;
        $this->transactionId = $transactionId;
        $this->amountPaid = $amountPaid;
        $this->change = $change;
        $this->otherData = $otherData;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }
    
    public function getCardLast4(): ?string
    {
        return $this->cardLast4;
    }
    
    public function getCardType(): ?string
    {
        return $this->cardType;
    }
    
    public function getApprovalCode(): ?string
    {
        return $this->approvalCode;
    }
    
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }
    
    public function getAmountPaid(): float
    {
        return $this->amountPaid;
    }
    
    public function getChange(): float
    {
        return $this->change;
    }
    
    public function getOtherData(): array
    {
        return $this->otherData;
    }

    public function toArray(): array
    {
        $result = [
            'method' => $this->method,
            'isValid' => $this->isValid,
            'cardLast4' => $this->cardLast4,
            'cardType' => $this->cardType,
            'approvalCode' => $this->approvalCode,
            'transactionId' => $this->transactionId,
            'amountPaid' => $this->amountPaid,
            'change' => $this->change
        ];
        
        // Add any other data
        foreach ($this->otherData as $key => $value) {
            if (!array_key_exists($key, $result)) {
                $result[$key] = $value;
            }
        }
        
        // Debug log to see what's being returned
        \Log::debug('PaymentDetails::toArray', [
            'result' => $result
        ]);
        
        return $result;
    }

    public static function fromArray(array $data): self
    {
        // Extract basic fields
        $method = $data['method'] ?? '';
        
        // Handle isValid - ensure it's a boolean
        $isValid = false;
        if (isset($data['isValid'])) {
            if (is_bool($data['isValid'])) {
                $isValid = $data['isValid'];
            } elseif (is_string($data['isValid']) && ($data['isValid'] === 'true' || $data['isValid'] === '1')) {
                $isValid = true;
            } elseif (is_numeric($data['isValid'])) {
                $isValid = (bool)$data['isValid'];
            }
            // If isValid has a non-boolean value (like 'visa'), we'll set it to true
            elseif (!empty($data['isValid'])) {
                $isValid = true;
            }
        }
        
        // Extract specific payment fields with proper type casting
        // Handle cardLast4 - can be null or string
        $cardLast4 = null;
        if (isset($data['cardLast4']) && $data['cardLast4'] !== null) {
            $cardLast4 = (string)$data['cardLast4'];
        }
        
        // Handle cardType - can be null or string
        $cardType = null;
        if (isset($data['cardType']) && $data['cardType'] !== null) {
            $cardType = (string)$data['cardType'];
        }
        
        // Handle approvalCode - can be null or string
        $approvalCode = null;
        if (isset($data['approvalCode']) && $data['approvalCode'] !== null) {
            $approvalCode = (string)$data['approvalCode'];
        }
        
        // Handle transactionId - can be null or string
        $transactionId = null;
        if (isset($data['transactionId']) && $data['transactionId'] !== null) {
            $transactionId = (string)$data['transactionId'];
        }
        
        // Handle numeric values
        $amountPaid = isset($data['amountPaid']) ? (float)$data['amountPaid'] : 0;
        $change = isset($data['change']) ? (float)$data['change'] : 0;
        
        // Collect any other fields
        $otherData = [];
        foreach ($data as $key => $value) {
            if (!in_array($key, ['method', 'isValid', 'cardLast4', 'cardType', 'approvalCode', 'transactionId', 'amountPaid', 'change'])) {
                $otherData[$key] = $value;
            }
        }
        

        
        // Create a new instance with all data properly organized
        return new self(
            $method, 
            $isValid, 
            $cardLast4, 
            $cardType, 
            $approvalCode, 
            $transactionId, 
            $amountPaid, 
            $change, 
            $otherData
        );
    }
}
