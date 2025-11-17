<?php
if (!isset($_SESSION['voter_id'])) {
    header('Location: index.php?screen=login');
    exit;
}

$voter_id = $_SESSION['voter_id'];
$department_id = $_SESSION['department_id'];
$election_id = $_SESSION['election_id'];

$voter_check = new Election('', $election_id, $voter_id, $department_id);
$has_voted = $voter_check->hasVoted();
$is_eligible = $voter_check->checkVoterEligibility();
$settings = $voter_check->getElectionSettings();
?>
<section id="verificationScreen" class="screen active">
    <div class="verification-container">
        <div class="card">
            <div class="verification-message <?php echo $is_eligible && !$has_voted ? 'verification-success' : 'verification-error'; ?>">
                <?php if (!$is_eligible): ?>
                    <h3>Not Eligible</h3>
                    <p>Your voter ID (<?php echo htmlspecialchars($voter_id); ?>) is not eligible to vote in this election.</p>
                    <p>Please contact the administrator if you believe this is an error.</p>
                <?php elseif ($has_voted): ?>
                    <h3>Already Voted</h3>
                    <p>Our records show that you have already cast your vote in this election.</p>
                    <?php if ($settings['allow_vote_changes']): ?>
                        <p>You may change your vote if you wish.</p>
                    <?php else: ?>
                        <p>Vote changes are not currently allowed.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <h3>Eligibility Confirmed</h3>
                    <p>You are eligible to vote in the Student Council Election!</p>
                    <?php if ($settings['voting_open']): ?>
                        <p>Voting is currently open. You may proceed to cast your vote.</p>
                    <?php else: ?>
                        <p>Voting is currently closed. Please check back later.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="verification-actions">
                <?php if ($is_eligible): ?>
                    <?php if ($has_voted): ?>
                        <?php if ($settings['allow_vote_changes']): ?>
                            <a href="?screen=ballot" class="btn" style="width: 100%; display: block; text-decoration: none; text-align: center; margin-bottom: 10px;">Change My Vote</a>
                        <?php endif; ?>
                        <?php if ($settings['results_visible']): ?>
                            <a href="?screen=results" class="btn <?php echo $settings['allow_vote_changes'] ? 'btn-outline' : ''; ?>" style="width: 100%; display: block; text-decoration: none; text-align: center;">View Results</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if ($settings['voting_open']): ?>
                            <a href="?screen=ballot" class="btn" style="width: 100%; display: block; text-decoration: none; text-align: center;">Proceed to Vote</a>
                        <?php else: ?>
                            <?php if ($settings['results_visible']): ?>
                                <a href="?screen=results" class="btn" style="width: 100%; display: block; text-decoration: none; text-align: center; margin-bottom: 10px;">View Results</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
                <a href="?screen=login" class="btn btn-outline" style="width: 100%; display: block; text-decoration: none; text-align: center;">Back to Login</a>
            </div>
        </div>
    </div>
</section>