import React from 'react';

interface LoadingBarProps {
    isLoading: boolean;
}

export function LoadingBar({ isLoading }: LoadingBarProps) {
    if (!isLoading) return null;

    return (
        <div className="fixed top-0 left-0 right-0 z-50">
            <div className="h-1 w-full bg-gray-200">
                <div 
                    className="h-1 bg-gradient-to-r from-blue-500 to-purple-500 animate-loading-bar"
                    style={{
                        width: '30%',
                    }}
                />
            </div>
        </div>
    );
}
