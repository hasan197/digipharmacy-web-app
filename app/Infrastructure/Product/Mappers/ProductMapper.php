<?php

namespace App\Infrastructure\Product\Mappers;

use App\Domain\Product\Models\Product as DomainProduct;
use App\Domain\Product\ValueObjects\ProductId;
use App\Domain\Product\ValueObjects\ProductStatus;
use App\Models\Product as EloquentProduct;
use DateTime;

class ProductMapper
{
    public function toDomain(EloquentProduct $eloquentProduct): DomainProduct
    {
        return DomainProduct::create(
            $eloquentProduct->name,
            $eloquentProduct->category_id,
            $eloquentProduct->price,
            $eloquentProduct->stock,
            $eloquentProduct->unit,
            $eloquentProduct->expiry_date ? new DateTime($eloquentProduct->expiry_date) : null,
            $eloquentProduct->description,
            $eloquentProduct->requires_prescription,
            $eloquentProduct->status ?? ProductStatus::ACTIVE,
            $eloquentProduct->sku,
            $eloquentProduct->barcode,
            $eloquentProduct->cost_price,
            $eloquentProduct->id,
            $eloquentProduct->created_at ? new DateTime($eloquentProduct->created_at) : null,
            $eloquentProduct->updated_at ? new DateTime($eloquentProduct->updated_at) : null
        );
    }

    public function toEloquent(DomainProduct $domainProduct): EloquentProduct
    {
        $product = new EloquentProduct();
        
        if ($domainProduct->getId()->getValue() > 0) {
            $product = EloquentProduct::find($domainProduct->getId()->getValue()) ?? $product;
        }
        
        $product->name = $domainProduct->getName();
        $product->category_id = $domainProduct->getCategoryId();
        $product->description = $domainProduct->getDescription();
        $product->price = $domainProduct->getPrice();
        $product->stock = $domainProduct->getStock();
        $product->unit = $domainProduct->getUnit();
        $product->expiry_date = $domainProduct->getExpiryDate() ? $domainProduct->getExpiryDate()->format('Y-m-d') : null;
        $product->requires_prescription = $domainProduct->getRequiresPrescription();
        $product->status = $domainProduct->getStatus()->getValue();
        $product->sku = $domainProduct->getSku();
        $product->barcode = $domainProduct->getBarcode();
        $product->cost_price = $domainProduct->getCostPrice();
        
        return $product;
    }
}
