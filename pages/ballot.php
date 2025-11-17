<?php
if (!isset($_SESSION['voter_id'])) {
    header('Location: index.php?screen=login');
    exit;
}

$election_id = $_SESSION['election_id'];
$election_obj = new Election('', $election_id);
$positions = $election_obj->getAllPositions();

// Group candidates by position
$candidates_by_position = [];
foreach ($positions as $position) {
    $position_obj = new Election('', $election_id, '', '', $position['position_id']);
    $candidates_by_position[$position['position_id']] = $position_obj->getCandidatesByPosition();
}
?>
<section id="ballotScreen" class="screen active">
    <div class="ballot-container">
        <div class="ballot-header">
            <h1>Cast Your Vote</h1>
            <p>Select candidates for each position</p>
        </div>
        
        <form action="process.php" method="POST" id="voteForm">
            <?php foreach ($positions as $position): ?>
                <?php if ($position['max_selections'] == 1): ?>
                    <!-- Single Selection Position -->
                    <div class="position-section">
                        <h2 class="position-title"><?php echo $position['position_name']; ?></h2>
                        <div class="candidates-grid">
                            <?php foreach ($candidates_by_position[$position['position_id']] as $candidate): ?>
                            <div class="candidate-card <?php echo strtolower($candidate['team_color']); ?>-team"
                                 onclick="selectCandidate('<?php echo strtolower(str_replace(' ', '_', $position['position_name'])); ?>', '<?php echo $candidate['candidate_id']; ?>')">
                                <div class="team-badge <?php echo strtolower($candidate['team_color']); ?>-team-badge">
                                    <?php echo ucfirst($candidate['team_color']); ?> Team
                                </div>
                                <input type="radio" name="<?php echo strtolower(str_replace(' ', '_', $position['position_name'])); ?>" 
                                       value="<?php echo $candidate['candidate_id']; ?>" 
                                       id="candidate_<?php echo $candidate['candidate_id']; ?>" 
                                       class="candidate-radio" required>
                                <div class="candidate-image">
                                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjRTVFN0VCIi8+CjxjaXJjbGUgY3g9IjUwIiBjeT0iNDAiIHI9IjIwIiBmaWxsPSIjOUI5QjlCIi8+CjxyZWN0IHg9IjMwIiB5PSI3MCIgd2lkdGg9IjQwIiBoZWlnaHQ9IjMwIiBmaWxsPSIjOUJCQjlCIi8+Cjwvc3ZnPg==" 
                                         alt="<?php echo $candidate['first_name'] . ' ' . $candidate['last_name']; ?>">
                                </div>
                                <div class="candidate-name"><?php echo $candidate['first_name'] . ' ' . $candidate['last_name']; ?></div>
                                <div class="candidate-bio"><?php echo $candidate['bio']; ?></div>
                                <div style="text-align: center;">
                                    <span class="vote-indicator"></span>
                                    Select Candidate
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Multiple Selection Position (Senators) -->
                    <div class="position-section">
                        <h2 class="position-title"><?php echo $position['position_name']; ?> (Select up to <?php echo $position['max_selections']; ?>)</h2>
                        <div class="senator-grid">
                            <?php foreach ($candidates_by_position[$position['position_id']] as $candidate): ?>
                            <div class="senator-card <?php echo strtolower($candidate['team_color']); ?>-team"
                                 onclick="selectSenator('senator_<?php echo $candidate['candidate_id']; ?>', <?php echo $position['max_selections']; ?>)">
                                <div class="team-badge <?php echo strtolower($candidate['team_color']); ?>-team-badge">
                                    <?php echo ucfirst($candidate['team_color']); ?> Team
                                </div>
                                <input type="checkbox" name="senator[]" 
                                       value="<?php echo $candidate['candidate_id']; ?>" 
                                       id="senator_<?php echo $candidate['candidate_id']; ?>" 
                                       class="senator-checkbox">
                                <div class="senator-image">
                                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjRTVFN0VCIi8+CjxjaXJjbGUgY3g9IjUwIiBjeT0iNDAiIHI9IjIwIiBmaWxsPSIjOUI5QjlCIi8+CjxyZWN0IHg9IjMwIiB5PSI3MCIgd2lkdGg9IjQwIiBoZWlnaHQ9IjMwIiBmaWxsPSIjOUJCQjlCIi8+Cjwvc3ZnPg==" 
                                         alt="<?php echo $candidate['first_name'] . ' ' . $candidate['last_name']; ?>">
                                </div>
                                <div class="senator-name"><?php echo $candidate['first_name'] . ' ' . $candidate['last_name']; ?></div>
                                <div class="candidate-bio"><?php echo $candidate['department_name']; ?></div>
                                <div style="text-align: center; margin-top: 10px;">
                                    <span class="senator-vote-indicator"></span>
                                    Select
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div style="margin-top: 15px; text-align: center;">
                            <span id="senatorSelectionCount">0/<?php echo $position['max_selections']; ?> selected</span>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <div class="card" style="text-align: center;">
                <button type="submit" name="submit_vote" class="btn" id="submitVoteBtn" disabled>Submit Vote</button>
            </div>
        </form>
    </div>
</section>