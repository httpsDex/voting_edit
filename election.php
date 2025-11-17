<?php
include_once 'dbh.php';

class Election extends Dbh {
    public $keyword;
    public $election_id;
    public $voter_identifier; // Changed from student_id
    public $department_id;
    public $position_id;
    public $candidate_id;
    public $team_id;

    public function __construct($keyword = '', $election_id = '', $voter_identifier = '', $department_id = '', $position_id = '', $candidate_id = '', $team_id = '') {
        $this->keyword = $keyword;
        $this->election_id = $election_id;
        $this->voter_identifier = $voter_identifier;
        $this->department_id = $department_id;
        $this->position_id = $position_id;
        $this->candidate_id = $candidate_id;
        $this->team_id = $team_id;
    }

    // NEW: Check if voter ID is eligible based on eligibility rules
    public function checkVoterEligibility() {
        $conn = $this->connect();
        $sql = "SELECT * FROM voter_eligibility 
                WHERE election_id = ? 
                AND department_id = ? 
                AND ? BETWEEN id_range_start AND id_range_end";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("iis", $this->election_id, $this->department_id, $this->voter_identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        $eligible = $result->num_rows > 0;
        $stmt->close();
        return $eligible;
    }

    // NEW: Get all eligibility rules for an election
    public function getEligibilityRules() {
        $conn = $this->connect();
        $sql = "SELECT ve.*, d.department_name 
                FROM voter_eligibility ve
                JOIN departments d ON ve.department_id = d.department_id
                WHERE ve.election_id = ?
                ORDER BY d.department_name";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $this->election_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rules = [];
        while ($row = $result->fetch_assoc()) {
            $rules[] = $row;
        }
        $stmt->close();
        return $rules;
    }

    // NEW: Add eligibility rule
    public function addEligibilityRule($id_range_start, $id_range_end) {
        $conn = $this->connect();
        $sql = "INSERT INTO voter_eligibility (election_id, department_id, id_range_start, id_range_end) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("iiss", $this->election_id, $this->department_id, $id_range_start, $id_range_end);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // NEW: Delete eligibility rule
    public function deleteEligibilityRule($eligibility_id) {
        $conn = $this->connect();
        $sql = "DELETE FROM voter_eligibility WHERE eligibility_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("i", $eligibility_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // STUDENT METHODS (For candidates only)
    public function getStudent() {
        $conn = $this->connect();
        $sql = "SELECT s.*, d.department_name FROM students s 
                LEFT JOIN departments d ON s.department_id = d.department_id 
                WHERE s.student_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("s", $this->keyword);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->num_rows > 0 ? $result->fetch_assoc() : false;
        $stmt->close();
        return $data;
    }

    public function saveStudent($first_name, $last_name) {
        $conn = $this->connect();
        $sql = "INSERT INTO students (student_id, first_name, last_name, department_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("sssi", $this->keyword, $first_name, $last_name, $this->department_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // DEPARTMENT METHODS
    public function getAllDepartments() {
        $conn = $this->connect();
        $sql = "SELECT * FROM departments ORDER BY department_name";
        $result = $conn->query($sql);
        $departments = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $departments[] = $row;
            }
        }
        return $departments;
    }

    public function getDepartmentById() {
        $conn = $this->connect();
        $sql = "SELECT * FROM departments WHERE department_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("i", $this->department_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->num_rows > 0 ? $result->fetch_assoc() : false;
        $stmt->close();
        return $data;
    }

    // ELECTION METHODS
    public function getCurrentElection() {
        $conn = $this->connect();
        $sql = "SELECT * FROM elections WHERE voting_open = 1 OR results_visible = 1 ORDER BY election_id DESC LIMIT 1";
        $result = $conn->query($sql);
        return $result && $result->num_rows > 0 ? $result->fetch_assoc() : false;
    }

    public function getElectionSettings() {
        $conn = $this->connect();
        $sql = "SELECT * FROM elections WHERE election_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("i", $this->election_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->num_rows > 0 ? $result->fetch_assoc() : false;
        $stmt->close();
        return $data;
    }

    public function updateElectionSettings($voting_open, $results_visible, $allow_vote_changes) {
        $conn = $this->connect();
        $sql = "UPDATE elections SET voting_open = ?, results_visible = ?, allow_vote_changes = ? WHERE election_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("iiii", $voting_open, $results_visible, $allow_vote_changes, $this->election_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // POSITION METHODS
    public function getAllPositions() {
        $conn = $this->connect();
        $sql = "SELECT * FROM positions ORDER BY position_order";
        $result = $conn->query($sql);
        $positions = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $positions[] = $row;
            }
        }
        return $positions;
    }

    // TEAM METHODS
    public function getAllTeams() {
        $conn = $this->connect();
        $sql = "SELECT * FROM teams ORDER BY team_id";
        $result = $conn->query($sql);
        $teams = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $teams[] = $row;
            }
        }
        return $teams;
    }

    // CANDIDATE METHODS
    public function getAllCandidates() {
        $conn = $this->connect();
        $sql = "SELECT c.*, s.first_name, s.last_name, p.position_name, t.team_color, d.department_name
                FROM candidates c
                JOIN students s ON c.student_id = s.student_id
                JOIN positions p ON c.position_id = p.position_id
                JOIN teams t ON c.team_id = t.team_id
                JOIN departments d ON s.department_id = d.department_id
                WHERE c.election_id = ?
                ORDER BY p.position_order, s.last_name";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $this->election_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $candidates = [];
        while ($row = $result->fetch_assoc()) {
            $candidates[] = $row;
        }
        $stmt->close();
        return $candidates;
    }

    public function getCandidatesByPosition() {
        $conn = $this->connect();
        $sql = "SELECT c.*, s.first_name, s.last_name, t.team_color, d.department_name
                FROM candidates c
                JOIN students s ON c.student_id = s.student_id
                JOIN teams t ON c.team_id = t.team_id
                JOIN departments d ON s.department_id = d.department_id
                WHERE c.election_id = ? AND c.position_id = ?
                ORDER BY s.last_name";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("ii", $this->election_id, $this->position_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $candidates = [];
        while ($row = $result->fetch_assoc()) {
            $candidates[] = $row;
        }
        $stmt->close();
        return $candidates;
    }

    public function saveCandidate($bio, $student_id) {
        $conn = $this->connect();
        $sql = "INSERT INTO candidates (student_id, election_id, position_id, team_id, bio) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("siiis", $student_id, $this->election_id, $this->position_id, $this->team_id, $bio);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function deleteCandidate() {
        $conn = $this->connect();
        $sql = "DELETE FROM candidates WHERE candidate_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("i", $this->candidate_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // VOTER METHODS - Modified for anonymous voting
    public function hasVoted() {
        $conn = $this->connect();
        $sql = "SELECT * FROM voters WHERE voter_identifier = ? AND election_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("si", $this->voter_identifier, $this->election_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $has_voted = $result->num_rows > 0;
        $stmt->close();
        return $has_voted;
    }

    public function registerVoter() {
        $conn = $this->connect();
        $sql = "INSERT INTO voters (voter_identifier, election_id, department_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("sii", $this->voter_identifier, $this->election_id, $this->department_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // VOTE METHODS
    public function saveVote() {
        $conn = $this->connect();
        $sql = "INSERT INTO votes (election_id, position_id, candidate_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("iii", $this->election_id, $this->position_id, $this->candidate_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function deleteVotesByVoter() {
        $conn = $this->connect();
        $sql = "DELETE FROM voters WHERE voter_identifier = ? AND election_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("si", $this->voter_identifier, $this->election_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // RESULTS METHODS
    public function getElectionResults() {
        $conn = $this->connect();
        $sql = "SELECT p.position_name, p.position_order,
                       CONCAT(s.first_name, ' ', s.last_name) as candidate_name,
                       t.team_color,
                       COUNT(v.vote_id) as vote_count,
                       (SELECT COUNT(DISTINCT vote_id) FROM votes WHERE position_id = p.position_id AND election_id = ?) as total_position_votes
                FROM positions p
                LEFT JOIN candidates c ON p.position_id = c.position_id AND c.election_id = ?
                LEFT JOIN students s ON c.student_id = s.student_id
                LEFT JOIN teams t ON c.team_id = t.team_id
                LEFT JOIN votes v ON c.candidate_id = v.candidate_id AND v.election_id = ?
                GROUP BY p.position_id, c.candidate_id
                ORDER BY p.position_order, vote_count DESC";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("iii", $this->election_id, $this->election_id, $this->election_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $results = [];
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        $stmt->close();
        return $results;
    }

    public function getElectionStatistics() {
        $conn = $this->connect();
        $stats = [];
        
        // Total voters (not students, but actual voters)
        $sql2 = "SELECT COUNT(*) as total FROM voters WHERE election_id = ?";
        $stmt2 = $conn->prepare($sql2);
        if ($stmt2) {
            $stmt2->bind_param("i", $this->election_id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $stats['voted_count'] = $result2->fetch_assoc()['total'];
            $stmt2->close();
        } else {
            $stats['voted_count'] = 0;
        }
        
        // Total votes cast
        $sql3 = "SELECT COUNT(*) as total FROM votes WHERE election_id = ?";
        $stmt3 = $conn->prepare($sql3);
        if ($stmt3) {
            $stmt3->bind_param("i", $this->election_id);
            $stmt3->execute();
            $result3 = $stmt3->get_result();
            $stats['total_votes'] = $result3->fetch_assoc()['total'];
            $stmt3->close();
        } else {
            $stats['total_votes'] = 0;
        }
        
        // Team votes
        $sql4 = "SELECT t.team_color, COUNT(v.vote_id) as vote_count
                 FROM votes v
                 JOIN candidates c ON v.candidate_id = c.candidate_id
                 JOIN teams t ON c.team_id = t.team_id
                 WHERE v.election_id = ?
                 GROUP BY t.team_id";
        $stmt4 = $conn->prepare($sql4);
        $stats['team_votes'] = [];
        if ($stmt4) {
            $stmt4->bind_param("i", $this->election_id);
            $stmt4->execute();
            $result4 = $stmt4->get_result();
            while ($row = $result4->fetch_assoc()) {
                $stats['team_votes'][$row['team_color']] = $row['vote_count'];
            }
            $stmt4->close();
        }
        
        return $stats;
    }

    // ADMIN METHODS
    public function verifyAdmin($username, $password) {
        $conn = $this->connect();
        $sql = "SELECT * FROM admin_users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            $stmt->close();
            if (password_verify($password, $admin['password_hash'])) {
                return $admin;
            }
        } else {
            $stmt->close();
        }
        return false;
    }
}
?>