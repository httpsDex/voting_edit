<?php
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php?screen=login');
    exit;
}

$election_id = $_SESSION['election_id'];
$election_obj = new Election('', $election_id);
$candidates = $election_obj->getAllCandidates();
$positions = $election_obj->getAllPositions();
$teams = $election_obj->getAllTeams();
$stats = $election_obj->getElectionStatistics();
$settings = $election_obj->getElectionSettings();
$departments = $election_obj->getAllDepartments();
$eligibility_rules = $election_obj->getEligibilityRules();
?>
<section id="adminScreen" class="screen active">
    <div class="admin-container">
        <div class="admin-header">
            <h1>Administrator Panel</h1>
            <p>Manage the Student Council Election</p>
            <p style="font-size: 14px; margin-top: 10px;">
                Welcome, <?php echo $_SESSION['admin_name']; ?> | 
                <a href="process.php?logout=1" style="color: #dc3545;">Logout</a>
            </p>
        </div>

        <?php if (isset($_SESSION['admin_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['admin_message']; unset($_SESSION['admin_message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['admin_error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['admin_error']; unset($_SESSION['admin_error']); ?></div>
        <?php endif; ?>

        <div class="card">
            <!-- System Settings -->
            <div class="admin-section">
                <h3>System Settings</h3>
                <form action="process.php" method="POST">
                    <div class="admin-controls">
                        <div>
                            <label>
                                <input type="checkbox" name="voting_open" value="1" <?php echo $settings['voting_open'] == '1' ? 'checked' : ''; ?>>
                                Voting Open
                            </label>
                        </div>
                        <div>
                            <label>
                                <input type="checkbox" name="results_visible" value="1" <?php echo $settings['results_visible'] == '1' ? 'checked' : ''; ?>>
                                Results Visible
                            </label>
                        </div>
                        <div>
                            <label>
                                <input type="checkbox" name="allow_vote_changes" value="1" <?php echo $settings['allow_vote_changes'] == '1' ? 'checked' : ''; ?>>
                                Allow Vote Changes
                            </label>
                        </div>
                    </div>
                    <button type="submit" name="update_settings" class="btn" style="margin-top: 15px;">Update Settings</button>
                </form>
            </div>

            <!-- Election Statistics -->
            <div class="admin-section">
                <h3>Election Statistics</h3>
                <div class="results-summary">
                    <div class="summary-card">
                        <div class="summary-value"><?php echo $stats['voted_count']; ?></div>
                        <div class="summary-label">Voters Count</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-value"><?php echo $stats['total_votes']; ?></div>
                        <div class="summary-label">Total Votes</div>
                    </div>
                    <?php foreach ($stats['team_votes'] as $team => $votes): ?>
                    <div class="summary-card">
                        <div class="summary-value"><?php echo $votes; ?></div>
                        <div class="summary-label"><?php echo ucfirst($team); ?> Team Votes</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Voter Eligibility Management -->
            <div class="admin-section">
                <h3>Voter Eligibility Management</h3>
                
                <!-- Add Eligibility Rule Form -->
                <div class="form-group">
                    <h4>Add Eligibility Rule</h4>
                    <p style="font-size: 14px; color: #666; margin-bottom: 15px;">
                        Set which voter IDs can vote. Format: A22-0001 (A = prefix, 22 = year, 0001 = number)
                    </p>
                    <form action="process.php" method="POST" class="candidate-form">
                        <div class="form-row">
                            <div>
                                <label for="eligibility_department">Department</label>
                                <select name="eligibility_department" id="eligibility_department" required>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['department_id']; ?>"><?php echo $dept['department_name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="id_range_start">ID Range Start</label>
                                <input type="text" name="id_range_start" id="id_range_start" placeholder="A22-0001" required>
                            </div>
                            <div>
                                <label for="id_range_end">ID Range End</label>
                                <input type="text" name="id_range_end" id="id_range_end" placeholder="A22-9999" required>
                            </div>
                            <div>
                                <button type="submit" name="add_eligibility" class="btn">Add Rule</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Eligibility Rules List -->
                <div class="candidate-list">
                    <h4>Current Eligibility Rules</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>ID Range Start</th>
                                <th>ID Range End</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($eligibility_rules)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">No eligibility rules set. Add rules to allow voters.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($eligibility_rules as $rule): ?>
                                <tr>
                                    <td><?php echo $rule['department_name']; ?></td>
                                    <td><?php echo $rule['id_range_start']; ?></td>
                                    <td><?php echo $rule['id_range_end']; ?></td>
                                    <td>
                                        <form action="process.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="eligibility_id" value="<?php echo $rule['eligibility_id']; ?>">
                                            <button type="submit" name="delete_eligibility" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('Are you sure you want to delete this rule?')">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Candidate Management -->
            <div class="admin-section">
                <h3>Candidate Management</h3>
                
                <!-- Add Candidate Form -->
                <div class="form-group">
                    <h4>Add New Candidate</h4>
                    <form action="process.php" method="POST" class="candidate-form">
                        <div class="form-row">
                            <div>
                                <label for="candidate_student_id">Student ID</label>
                                <input type="text" name="candidate_student_id" id="candidate_student_id" required>
                            </div>
                            <div>
                                <label for="candidate_position">Position</label>
                                <select name="candidate_position" id="candidate_position" required>
                                    <?php foreach ($positions as $position): ?>
                                        <option value="<?php echo $position['position_id']; ?>"><?php echo $position['position_name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="candidate_team">Team</label>
                                <select name="candidate_team" id="candidate_team" required>
                                    <?php foreach ($teams as $team): ?>
                                        <option value="<?php echo $team['team_id']; ?>"><?php echo ucfirst($team['team_color']); ?> Team</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="candidate_bio">Bio</label>
                                <input type="text" name="candidate_bio" id="candidate_bio" required>
                            </div>
                            <div>
                                <button type="submit" name="add_candidate" class="btn">Add Candidate</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Candidates List -->
                <div class="candidate-list">
                    <h4>Current Candidates</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Team</th>
                                <th>Bio</th>
                                <th>Department</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidates as $candidate): ?>
                            <tr>
                                <td><?php echo $candidate['student_id']; ?></td>
                                <td><?php echo $candidate['first_name'] . ' ' . $candidate['last_name']; ?></td>
                                <td><?php echo $candidate['position_name']; ?></td>
                                <td><?php echo ucfirst($candidate['team_color']); ?> Team</td>
                                <td><?php echo $candidate['bio']; ?></td>
                                <td><?php echo $candidate['department_name']; ?></td>
                                <td>
                                    <form action="process.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="candidate_id" value="<?php echo $candidate['candidate_id']; ?>">
                                        <button type="submit" name="delete_candidate" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Are you sure you want to delete this candidate?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <a href="?screen=results" class="btn">View Results</a>
                <a href="?screen=login" class="btn btn-outline">Back to Login</a>
            </div>
        </div>
    </div>
</section>