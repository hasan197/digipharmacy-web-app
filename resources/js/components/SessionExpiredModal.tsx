import React from 'react';

interface SessionExpiredModalProps {
    isOpen: boolean;
    onClose: () => void;
}

const SessionExpiredModal: React.FC<SessionExpiredModalProps> = ({ isOpen, onClose }) => {
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center">
            {/* Backdrop */}
            <div className="fixed inset-0 bg-black/50" onClick={onClose} />
            
            {/* Modal */}
            <div className="relative bg-white rounded-lg shadow-xl p-6 w-96 max-w-md mx-4">
                {/* Icon */}
                <div className="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg className="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                
                {/* Title */}
                <h3 className="text-lg font-medium text-gray-900 text-center mb-2">
                    Sesi Berakhir
                </h3>
                
                {/* Message */}
                <p className="text-sm text-gray-500 text-center mb-6">
                    Sesi Anda telah berakhir. Silakan login kembali untuk melanjutkan.
                </p>
                
                {/* Button */}
                <div className="flex justify-center">
                    <button
                        onClick={onClose}
                        className="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                    >
                        Login Kembali
                    </button>
                </div>
            </div>
        </div>
    );
};

export default SessionExpiredModal;
