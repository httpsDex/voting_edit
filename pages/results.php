<?php
$election_id = $_SESSION['election_id'];
$election_obj = new Election('', $election_id);
$settings = $election_obj->getElectionSettings();

if (!$settings['results_visible'] && !isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php?screen=login');
    exit;
}

$stats = $election_obj->getElectionStatistics();
$results = $election_obj->getElectionResults();

// Group results by position
$results_by_position = [];
foreach ($results as $result) {
    $results_by_position[$result['position_name']][] = $result;
}
?>
<section id="resultsScreen" class="screen active">
    <div class="results-container">
        <div class="results-header">
            <h1>Election Results</h1>
            <p>Live results for Student Council Officers</p>
        </div>
        <div class="results-summary">
            <div class="summary-card">
                <div class="summary-value"><?php echo $stats['total_votes']; ?></div>
                <div class="summary-label">Total Votes</div>
            </div>
            <div class="summary-card">
                <div class="summary-value"><?php echo $stats['voted_count']; ?></div>
                <div class="summary-label">Voters Participated</div>
            </div>
            <?php foreach ($stats['team_votes'] as $team => $votes): ?>
            <div class="summary-card">
                <div class="summary-value"><?php echo $votes; ?></div>
                <div class="summary-label"><?php echo ucfirst($team); ?> Team Votes</div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php foreach ($results_by_position as $position => $position_results): ?>
        <div class="card" style="margin-bottom: 30px;">
            <h3><?php echo $position; ?> Results</h3>
            <?php foreach ($position_results as $result): ?>
                <?php if ($result['candidate_name']): ?>
                    <?php 
                    $percentage = $result['total_position_votes'] > 0 
                        ? number_format(($result['vote_count'] / $result['total_position_votes']) * 100, 1) 
                        : 0;
                    ?>
                    <div class="result-item">
                        <div class="result-header">
                            <span><?php echo $result['candidate_name']; ?> (<?php echo ucfirst($result['team_color']); ?> Team)</span>
                            <span><?php echo $result['vote_count']; ?> votes (<?php echo $percentage; ?>%)</span>
                        </div>
                        <div class="result-bar-container">
                            <div class="result-bar" style="width: <?php echo $percentage; ?>%"><?php echo $percentage; ?>%</div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="?screen=login" class="btn btn-outline">Back to Login</a>
        </div>
    </div>
</section>