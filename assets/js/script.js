// JavaScript for election system
document.addEventListener('DOMContentLoaded', function() {
    // Admin modal functionality
    const adminToggle = document.getElementById('adminToggle');
    const adminModal = document.getElementById('adminLoginModal');
    const cancelAdminLogin = document.getElementById('cancelAdminLogin');
    
    if (adminToggle && adminModal) {
        adminToggle.addEventListener('click', function(e) {
            e.preventDefault();
            adminModal.classList.add('active');
        });
    }
    
    if (cancelAdminLogin && adminModal) {
        cancelAdminLogin.addEventListener('click', function() {
            adminModal.classList.remove('active');
        });
    }
    
    // Close modals when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('active');
        }
    });
});

// Global functions for ballot interaction
let selectedSenators = [];

function selectCandidate(position, candidateId) {
    // Deselect all candidates for this position
    document.querySelectorAll(`.candidate-card[onclick*="${position}"]`).forEach(card => {
        card.classList.remove('selected');
    });
    
    // Select the clicked candidate
    const selectedCard = document.querySelector(`.candidate-card[onclick*="${candidateId}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
    }
    
    // Check the radio button
    const radio = document.getElementById('candidate_' + candidateId);
    if (radio) {
        radio.checked = true;
    }
    
    updateSubmitButtonState();
}

// FIXED: Now properly handles the senator selection with max limit
function selectSenator(candidateId, maxSelections) {
    const checkbox = document.getElementById(candidateId);
    const card = checkbox ? checkbox.closest('.senator-card') : null;
    
    if (!checkbox || !card) return;
    
    if (checkbox.checked) {
        // Deselect
        checkbox.checked = false;
        card.classList.remove('selected');
        selectedSenators = selectedSenators.filter(id => id !== candidateId);
    } else {
        // Select if under limit
        if (selectedSenators.length < maxSelections) {
            checkbox.checked = true;
            card.classList.add('selected');
            selectedSenators.push(candidateId);
        } else {
            alert('You can only select up to ' + maxSelections + ' senators.');
        }
    }
    
    updateSenatorSelectionCount();
    updateSubmitButtonState();
}

function updateSenatorSelectionCount() {
    const countElement = document.getElementById('senatorSelectionCount');
    if (countElement) {
        const maxSelections = countElement.textContent.split('/')[1].split(' ')[0];
        countElement.textContent = `${selectedSenators.length}/${maxSelections} selected`;
    }
}

function updateSubmitButtonState() {
    const submitBtn = document.getElementById('submitVoteBtn');
    if (!submitBtn) return;
    
    // Check if all required positions have selections
    const presidentSelected = document.querySelector('input[name="president"]:checked');
    const vpSelected = document.querySelector('input[name="vice_president"]:checked');
    const secretarySelected = document.querySelector('input[name="secretary"]:checked');
    const treasurerSelected = document.querySelector('input[name="treasurer"]:checked');
    const auditorSelected = document.querySelector('input[name="auditor"]:checked');
    
    const allSelected = presidentSelected && vpSelected && secretarySelected && 
                       treasurerSelected && auditorSelected && selectedSenators.length > 0;
    
    submitBtn.disabled = !allSelected;
}

// Initialize when page loads
if (document.getElementById('senatorSelectionCount')) {
    updateSenatorSelectionCount();
    updateSubmitButtonState();
}