<?php
session_start();
include_once 'election.php';

$election = new Election();
$current_election = $election->getCurrentElection();
$election_id = $current_election['election_id'];

// VOTER LOGIN
if (isset($_POST['login'])) {
    $voter_id = strtoupper(trim($_POST['voter_id']));
    $department_id = $_POST['department_id'];
    
    // Basic format validation (A22-0001 format)
    if (!preg_match('/^A\d{2}-\d{4}$/', $voter_id)) {
        $_SESSION['login_error'] = 'Invalid voter ID format. Use format: A22-0001';
        header('Location: index.php?screen=login');
        exit;
    }
    
    // Check eligibility
    $voter_check = new Election('', $election_id, $voter_id, $department_id);
    $is_eligible = $voter_check->checkVoterEligibility();
    
    if (!$is_eligible) {
        $_SESSION['login_error'] = 'Your voter ID is not eligible for this election.';
        header('Location: index.php?screen=login');
        exit;
    }
    
    $_SESSION['voter_id'] = $voter_id;
    $_SESSION['department_id'] = $department_id;
    $_SESSION['election_id'] = $election_id;
    header('Location: index.php?screen=verification');
    exit;
}

// SUBMIT VOTE - FIXED with receipt generation
if (isset($_POST['submit_vote'])) {
    if (!isset($_SESSION['voter_id'])) {
        header('Location: index.php?screen=login');
        exit;
    }
    
    $voter_id = $_SESSION['voter_id'];
    $department_id = $_SESSION['department_id'];
    $election_id = $_SESSION['election_id'];
    
    $voter_check = new Election('', $election_id, $voter_id, $department_id);
    
    // Check eligibility again
    if (!$voter_check->checkVoterEligibility()) {
        $_SESSION['vote_error'] = 'You are not eligible to vote.';
        header('Location: index.php?screen=verification');
        exit;
    }
    
    // Check if already voted and vote changes not allowed
    $settings = $voter_check->getElectionSettings();
    if ($voter_check->hasVoted() && !$settings['allow_vote_changes']) {
        $_SESSION['vote_error'] = 'You have already voted and changes are not allowed.';
        header('Location: index.php?screen=verification');
        exit;
    }
    
    // If allowing vote changes, delete existing voter record
    if ($voter_check->hasVoted() && $settings['allow_vote_changes']) {
        $voter_check->deleteVotesByVoter();
    }
    
    // Save votes for single positions
    $single_positions = ['president', 'vice_president', 'secretary', 'treasurer', 'auditor'];
    $voted_candidates = []; // Store for receipt
    
    foreach ($single_positions as $position_key) {
        if (isset($_POST[$position_key])) {
            $candidate_id = $_POST[$position_key];
            
            // Get position_id and candidate name from candidate
            $sql = "SELECT c.position_id, p.position_name, CONCAT(s.first_name, ' ', s.last_name) as candidate_name 
                    FROM candidates c 
                    JOIN positions p ON c.position_id = p.position_id
                    JOIN students s ON c.student_id = s.student_id
                    WHERE c.candidate_id = ?";
            $stmt = $voter_check->connect()->prepare($sql);
            $stmt->bind_param("i", $candidate_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $position_data = $result->fetch_assoc();
            $stmt->close();
            
            $vote = new Election('', $election_id, $voter_id, $department_id, $position_data['position_id'], $candidate_id);
            $vote->saveVote();
            
            // Store for receipt
            $voted_candidates[$position_data['position_name']] = $position_data['candidate_name'];
        }
    }
    
    // Save senator votes
    if (isset($_POST['senator']) && is_array($_POST['senator'])) {
        $senator_names = [];
        foreach ($_POST['senator'] as $candidate_id) {
            $sql = "SELECT c.position_id, p.position_name, CONCAT(s.first_name, ' ', s.last_name) as candidate_name 
                    FROM candidates c 
                    JOIN positions p ON c.position_id = p.position_id
                    JOIN students s ON c.student_id = s.student_id
                    WHERE c.candidate_id = ?";
            $stmt = $voter_check->connect()->prepare($sql);
            $stmt->bind_param("i", $candidate_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $position_data = $result->fetch_assoc();
            $stmt->close();
            
            $vote = new Election('', $election_id, $voter_id, $department_id, $position_data['position_id'], $candidate_id);
            $vote->saveVote();
            
            // Store for receipt
            $senator_names[] = $position_data['candidate_name'];
        }
        if (!empty($senator_names)) {
            $voted_candidates['Senator'] = implode(', ', $senator_names);
        }
    }
    
    // Register voter if not already registered
    if (!$voter_check->hasVoted()) {
        $voter_check->registerVoter();
    }
    
    // Generate unique receipt code using timestamp + voter_id hash
    $receipt_code = strtoupper(substr(md5($voter_id . time() . $election_id), 0, 12));
    $receipt_code = chunk_split($receipt_code, 4, '-'); // Format: XXXX-XXXX-XXXX
    $receipt_code = rtrim($receipt_code, '-');
    
    // Store receipt data in session
    $_SESSION['vote_receipt'] = [
        'code' => $receipt_code,
        'timestamp' => date('Y-m-d H:i:s'),
        'election_name' => $current_election['election_name'],
        'votes' => $voted_candidates
    ];
    
    unset($_SESSION['voter_id']);
    unset($_SESSION['department_id']);
    $_SESSION['vote_success'] = 'Vote submitted successfully!';
    header('Location: index.php?screen=receipt');
    exit;
}

// ADMIN LOGIN
if (isset($_POST['admin_login'])) {
    $username = trim($_POST['admin_username']);
    $password = $_POST['admin_password'];
    
    $admin_check = new Election();
    $admin = $admin_check->verifyAdmin($username, $password);
    
    if ($admin) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        header('Location: index.php?screen=admin');
        exit;
    } else {
        $_SESSION['admin_error'] = 'Invalid username or password';
        header('Location: index.php?screen=login');
        exit;
    }
}

// ADD CANDIDATE
if (isset($_POST['add_candidate'])) {
    if (!isset($_SESSION['admin_logged_in'])) {
        header('Location: index.php?screen=login');
        exit;
    }
    
    $student_id = trim($_POST['candidate_student_id']);
    $position_id = $_POST['candidate_position'];
    $team_id = $_POST['candidate_team'];
    $bio = trim($_POST['candidate_bio']);
    $election_id = $_SESSION['election_id'];
    
    $candidate = new Election('', $election_id, '', '', $position_id, '', $team_id);
    
    if ($candidate->saveCandidate($bio, $student_id)) {
        $_SESSION['admin_message'] = 'Candidate added successfully!';
    } else {
        $_SESSION['admin_error'] = 'Error adding candidate!';
    }
    
    header('Location: index.php?screen=admin');
    exit;
}

// DELETE CANDIDATE
if (isset($_POST['delete_candidate'])) {
    if (!isset($_SESSION['admin_logged_in'])) {
        header('Location: index.php?screen=login');
        exit;
    }
    
    $candidate_id = $_POST['candidate_id'];
    $candidate = new Election('', '', '', '', '', $candidate_id);
    
    if ($candidate->deleteCandidate()) {
        $_SESSION['admin_message'] = 'Candidate deleted successfully!';
    } else {
        $_SESSION['admin_error'] = 'Error deleting candidate!';
    }
    
    header('Location: index.php?screen=admin');
    exit;
}

// ADD ELIGIBILITY RULE
if (isset($_POST['add_eligibility'])) {
    if (!isset($_SESSION['admin_logged_in'])) {
        header('Location: index.php?screen=login');
        exit;
    }
    
    $department_id = $_POST['eligibility_department'];
    $id_range_start = strtoupper(trim($_POST['id_range_start']));
    $id_range_end = strtoupper(trim($_POST['id_range_end']));
    $election_id = $_SESSION['election_id'];
    
    // Validate format
    if (!preg_match('/^A\d{2}-\d{4}$/', $id_range_start) || !preg_match('/^A\d{2}-\d{4}$/', $id_range_end)) {
        $_SESSION['admin_error'] = 'Invalid ID format. Use format: A22-0001';
        header('Location: index.php?screen=admin');
        exit;
    }
    
    $eligibility = new Election('', $election_id, '', $department_id);
    
    if ($eligibility->addEligibilityRule($id_range_start, $id_range_end)) {
        $_SESSION['admin_message'] = 'Eligibility rule added successfully!';
    } else {
        $_SESSION['admin_error'] = 'Error adding eligibility rule!';
    }
    
    header('Location: index.php?screen=admin');
    exit;
}

// DELETE ELIGIBILITY RULE
if (isset($_POST['delete_eligibility'])) {
    if (!isset($_SESSION['admin_logged_in'])) {
        header('Location: index.php?screen=login');
        exit;
    }
    
    $eligibility_id = $_POST['eligibility_id'];
    $eligibility = new Election();
    
    if ($eligibility->deleteEligibilityRule($eligibility_id)) {
        $_SESSION['admin_message'] = 'Eligibility rule deleted successfully!';
    } else {
        $_SESSION['admin_error'] = 'Error deleting eligibility rule!';
    }
    
    header('Location: index.php?screen=admin');
    exit;
}

// UPDATE SETTINGS
if (isset($_POST['update_settings'])) {
    if (!isset($_SESSION['admin_logged_in'])) {
        header('Location: index.php?screen=login');
        exit;
    }
    
    $election_id = $_SESSION['election_id'];
    $voting_open = isset($_POST['voting_open']) ? 1 : 0;
    $results_visible = isset($_POST['results_visible']) ? 1 : 0;
    $allow_vote_changes = isset($_POST['allow_vote_changes']) ? 1 : 0;
    
    $settings = new Election('', $election_id);
    if ($settings->updateElectionSettings($voting_open, $results_visible, $allow_vote_changes)) {
        $_SESSION['admin_message'] = 'Settings updated successfully!';
    } else {
        $_SESSION['admin_error'] = 'Error updating settings!';
    }
    
    header('Location: index.php?screen=admin');
    exit;
}

// ADMIN LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php?screen=login');
    exit;
}
?>