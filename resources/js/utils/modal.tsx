import { createRoot } from 'react-dom/client';
import SessionExpiredModal from '../components/SessionExpiredModal';

export const showSessionExpiredModal = () => {
    // Create modal container if it doesn't exist
    let modalContainer = document.getElementById('session-expired-modal');
    if (!modalContainer) {
        modalContainer = document.createElement('div');
        modalContainer.id = 'session-expired-modal';
        document.body.appendChild(modalContainer);
    }

    // Render modal
    const root = createRoot(modalContainer);
    root.render(
        <SessionExpiredModal 
            isOpen={true} 
            onClose={() => {
                root.unmount();
                window.location.href = '/login';
            }} 
        />
    );
};
