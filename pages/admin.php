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
$all_elections = $election_obj->getAllElections();

// Get results data for PDF
$results = $election_obj->getElectionResults();
$results_by_position = [];
foreach ($results as $result) {
    $results_by_position[$result['position_name']][] = $result;
}
?>
<section id="adminScreen" class="screen active">
    <div class="admin-container">
        <div class="admin-header">
            <h1>Administrator Panel</h1>
            <h3>Manage the Student Council Election</h3>
            <h3 style="font-size: 14px; margin-top: 10px;">
                Welcome, <?php echo $_SESSION['admin_name']; ?> | 
                <a href="process.php?logout=1" style="color: #dc3545;">Logout</a>
            </h3>
        </div>

        <?php if (isset($_SESSION['admin_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['admin_message']; unset($_SESSION['admin_message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['admin_error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['admin_error']; unset($_SESSION['admin_error']); ?></div>
        <?php endif; ?>

        <div class="card">
            <!-- Election Manager -->
            <div class="admin-section">
                <h3>Election Manager</h3>
                
                <!-- Create New Election -->
                <div class="form-group">
                    <h4>Create New Election</h4>
                    <form action="process.php" method="POST" class="candidate-form">
                        <div class="form-row">
                            <div>
                                <label for="election_name">Election Name</label>
                                <input type="text" name="election_name" id="election_name" placeholder="e.g., Student Council Election 2024" required>
                            </div>
                            <div>
                                <label for="election_year">Election Year</label>
                                <input type="number" name="election_year" id="election_year" min="2020" max="2030" value="<?php echo date('Y'); ?>" required>
                            </div>
                            <div>
                                <label for="start_date">Start Date</label>
                                <input type="datetime-local" name="start_date" id="start_date" required>
                            </div>
                            <div>
                                <label for="end_date">End Date</label>
                                <input type="datetime-local" name="end_date" id="end_date" required>
                            </div>
                            <div>
                                <button type="submit" name="create_election" class="btn">Create Election</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Set Active Election -->
                <div class="form-group">
                    <h4>Set Active Election</h4>
                    <form action="process.php" method="POST">
                        <div class="form-row">
                            <div style="flex: 1;">
                                <label for="election_id">Select Election</label>
                                <select name="election_id" id="election_id" required>
                                    <option value="">Select an election</option>
                                    <?php foreach ($all_elections as $election): ?>
                                        <option value="<?php echo $election['election_id']; ?>" 
                                                <?php echo $election['election_id'] == $election_id ? 'selected' : ''; ?>>
                                            <?php echo $election['election_name'] . ' (' . $election['election_year'] . ')'; ?>
                                            <?php echo $election['voting_open'] ? ' - ACTIVE' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <button type="submit" name="set_active_election" class="btn">Set as Active</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Download Results -->
                <div class="admin-section">
                    <h3>Download Section</h3>

                    <div style="background: #f8e6e9; border: 1px solid #d4a5ad; border-radius: 5px; padding: 20px; margin-bottom: 20px;">
                        <h4 style="color: #7a1f2a; margin-bottom: 15px;">📥 Downloadable Files</h4>
                        <p style="color: #7a1f2a; margin-bottom: 15px;">
                            You can download the generated vote results and statistics here.
                        </p>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <button type="button" id="generatePdf" class="btn" style="width: 100%; background-color: #7a1f2a; color: white;">
                                Download Results PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

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
                                <label for="candidate_first_name">First Name</label>
                                <input type="text" name="candidate_first_name" id="candidate_first_name" required>
                            </div>
                            <div>
                                <label for="candidate_last_name">Last Name</label>
                                <input type="text" name="candidate_last_name" id="candidate_last_name" required>
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
                                <label for="candidate_department">Department</label>
                                <select name="candidate_department" id="candidate_department" required>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['department_id']; ?>"><?php echo $dept['department_name']; ?></option>
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
                            <?php if (empty($candidates)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No candidates added yet.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($candidates as $candidate): ?>
                                <tr>
                                    <td><?php echo $candidate['candidate_id']; ?></td>
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
                            <?php endif; ?>
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

<!-- Hidden data container for PDF generation -->
<div id="pdfData" 
     data-election-name="<?php echo htmlspecialchars($settings['election_name']); ?>"
     data-generated-date="<?php echo date('F j, Y g:i A'); ?>"
     data-voted-count="<?php echo $stats['voted_count']; ?>"
     data-total-votes="<?php echo $stats['total_votes']; ?>"
     data-team-votes='<?php echo json_encode($stats['team_votes']); ?>'
     data-results='<?php echo json_encode($results_by_position); ?>'
     style="display: none;">
</div>

<!-- Include jsPDF library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
// Wait for jsPDF to load
window.jsPDF = window.jspdf.jsPDF;

document.getElementById('generatePdf').addEventListener('click', function() {
    generatePDF();
});

function generatePDF() {
    // Get data from hidden container
    const pdfData = document.getElementById('pdfData');
    const electionName = pdfData.getAttribute('data-election-name');
    const generatedDate = pdfData.getAttribute('data-generated-date');
    const votedCount = pdfData.getAttribute('data-voted-count');
    const totalVotes = pdfData.getAttribute('data-total-votes');
    const teamVotes = JSON.parse(pdfData.getAttribute('data-team-votes'));
    const results = JSON.parse(pdfData.getAttribute('data-results'));
    
    // Create new PDF instance
    const doc = new jsPDF();
    
    // Set document properties
    doc.setProperties({
        title: 'Election Results - ' + electionName,
        subject: 'Election Results Report',
        author: 'University Election System',
        creator: 'University Election System'
    });
    
    // Add header
    doc.setFontSize(20);
    doc.setFont('helvetica', 'bold');
    doc.text('ELECTION RESULTS', 105, 20, { align: 'center' });
    
    doc.setFontSize(14);
    doc.setFont('helvetica', 'normal');
    doc.text(electionName, 105, 30, { align: 'center' });
    
    doc.setFontSize(10);
    doc.text('Generated on: ' + generatedDate, 105, 38, { align: 'center' });
    
    // Add statistics section
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.text('ELECTION STATISTICS', 20, 50);
    
    doc.setFont('helvetica', 'normal');
    doc.text('Total Voters: ' + votedCount, 20, 60);
    doc.text('Total Votes Cast: ' + totalVotes, 20, 67);
    
    // Team statistics
    let teamY = 77;
    doc.setFont('helvetica', 'bold');
    doc.text('Team Statistics:', 20, teamY);
    doc.setFont('helvetica', 'normal');
    teamY += 7;
    
    for (const [team, votes] of Object.entries(teamVotes)) {
        doc.text(team.charAt(0).toUpperCase() + team.slice(1) + ' Team: ' + votes + ' votes', 25, teamY);
        teamY += 7;
    }
    
    // Results by position
    let yPosition = teamY + 10;
    
    for (const [position, candidates] of Object.entries(results)) {
        // Check if we need a new page
        if (yPosition > 250) {
            doc.addPage();
            yPosition = 20;
        }
        
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(12);
        doc.text(position.toUpperCase() + ' RESULTS', 20, yPosition);
        yPosition += 8;
        
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        
        // Create table data
        const tableData = [];
        candidates.forEach(candidate => {
            if (candidate.candidate_name) {
                const percentage = candidate.total_position_votes > 0 
                    ? ((candidate.vote_count / candidate.total_position_votes) * 100).toFixed(1)
                    : '0.0';
                
                tableData.push([
                    candidate.candidate_name,
                    candidate.team_color.charAt(0).toUpperCase() + candidate.team_color.slice(1) + ' Team',
                    candidate.vote_count + ' (' + percentage + '%)'
                ]);
            }
        });
        
        // Add table
        doc.autoTable({
            startY: yPosition,
            head: [['Candidate Name', 'Team', 'Votes (%)']],
            body: tableData,
            theme: 'grid',
            styles: { fontSize: 9, cellPadding: 3 },
            headStyles: { fillColor: [128, 0, 0] } // Maroon color
        });
        
        yPosition = doc.lastAutoTable.finalY + 10;
    }
    
    // Save the PDF
    doc.save('election_results_' + new Date().toISOString().slice(0, 10) + '.pdf');
}
</script>