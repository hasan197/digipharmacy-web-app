import React from 'react';

interface InventoryCardProps {
    name: string
    category: string
    price: string
    stock: number
    requires_prescription?: boolean
    onAddToCart: () => void
    isLoading?: boolean
  }
  
  function SkeletonCard() {
    return (
      <div className="w-full rounded-xl border border-gray-100 bg-white p-4 shadow-sm animate-pulse">
        <div className="space-y-3">
          <div className="h-5 w-1/3 rounded bg-gray-100" />
          <div className="h-6 w-3/4 rounded-lg bg-gray-100" />
          <div className="h-6 w-1/2 rounded bg-gray-100" />
          <div className="flex items-center justify-between pt-2">
            <div className="h-8 w-1/3 rounded bg-gray-100" />
            <div className="h-9 w-1/3 rounded-lg bg-gray-100" />
          </div>
        </div>
      </div>
    )
  }
  
  export default function InventoryCard({ isLoading = false, ...props }: InventoryCardProps) {
    if (isLoading) {
      return <SkeletonCard />
    }
  
    return (
      <div className="group relative w-full overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
        {/* Gradient Overlay */}
        <div className="absolute inset-0 bg-gradient-to-b from-purple-50/50 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
  
        {/* Card Content */}
        <div className="relative p-4">
          <div className="space-y-4">
            {/* Category Badge */}
            <div>
              <span className="inline-block rounded-md bg-purple-50 px-2.5 py-1 text-xs font-medium text-purple-700 shadow-sm transition-shadow duration-300 group-hover:shadow-purple-100/50">
                {props.category}
              </span>
            </div>
  
            {/* Medicine Name */}
            <div className="flex items-center gap-2">
              <h3 className="line-clamp-2 text-base font-semibold text-gray-900">{props.name}</h3>
              {props.requires_prescription && (
                <span className="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">
                  Rx
                </span>
              )}
            </div>
  
            {/* Stock Info */}
            <div className="flex items-center gap-2">
              <span className={`h-2 w-2 rounded-full ${props.stock > 10 ? "bg-green-400" : "bg-orange-400"}`} />
              <span className="text-sm text-gray-600">
                {props.stock} {props.stock === 1 ? "unit" : "units"} tersedia
              </span>
            </div>
  
            {/* Price Section */}
            <div className="flex items-baseline justify-between border-t border-gray-100 pt-3">
              <span className="text-xs text-gray-500">Harga</span>
              <span className="text-lg font-bold text-gray-900">{props.price}</span>
            </div>
          </div>
  
          {/* Add to Cart Button */}
          <div className="mt-4">
            <button
              onClick={props.onAddToCart}
              className="flex w-full items-center justify-center gap-2 rounded-lg bg-purple-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-purple-700 hover:shadow-md hover:shadow-purple-200/50 active:bg-purple-800 group-hover:scale-[1.02]"
            >
              <svg
                className="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
              >
                <path d="M12 5v14M5 12h14" />
              </svg>
              Add to Cart
            </button>
          </div>
        </div>
      </div>
    )
  }