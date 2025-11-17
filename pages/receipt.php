<?php
// NEW FILE: Displays unique voting receipt after successful vote submission
if (!isset($_SESSION['vote_receipt'])) {
    header('Location: index.php?screen=login');
    exit;
}

$receipt = $_SESSION['vote_receipt'];
?>
<section id="receiptScreen" class="screen active">
    <div class="verification-container">
        <div class="card" style="border-left: 5px solid var(--success-color);">
            <div class="verification-message verification-success">
                <h2 style="color: var(--success-color); margin-bottom: 10px;">✓ Vote Successfully Submitted!</h2>
                <p>Thank you for participating in the election.</p>
            </div>
            
            <!-- Receipt Section -->
            <div id="voteReceipt" style="background: var(--white-secondary); padding: 30px; border-radius: var(--border-radius); margin: 20px 0; border: 2px dashed var(--maroon-primary);">
                <div style="text-align: center; margin-bottom: 25px;">
                    <h2 style="color: var(--maroon-primary); margin-bottom: 5px;">VOTING RECEIPT</h2>
                    <p style="font-size: 14px; color: var(--gray-dark);"><?php echo $receipt['election_name']; ?></p>
                </div>
                
                <div style="background: var(--white-primary); padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <div style="text-align: center; margin-bottom: 15px;">
                        <div style="font-size: 12px; color: var(--gray-medium); margin-bottom: 5px;">RECEIPT CODE</div>
                        <div style="font-size: 28px; font-weight: bold; color: var(--maroon-primary); letter-spacing: 2px; font-family: monospace;">
                            <?php echo $receipt['code']; ?>
                        </div>
                    </div>
                    
                    <div style="border-top: 1px solid var(--gray-light); padding-top: 15px;">
                        <div style="font-size: 12px; color: var(--gray-medium); text-align: center;">
                            <strong>Date & Time:</strong> <?php echo $receipt['timestamp']; ?>
                        </div>
                    </div>
                </div>
                
                
                <div style="margin-top: 20px; padding: 15px; background: rgba(255, 193, 7, 0.1); border-left: 4px solid var(--warning-color); border-radius: 5px;">
                    <p style="font-size: 13px; color: var(--gray-dark); margin: 0;">
                        <strong>Important:</strong> Please screenshot this receipt. Show it to voting watchers for verification. This code confirms your participation in the election.
                    </p>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <button onclick="window.print()" class="btn" style="margin-right: 10px;">
                    Print Receipt
                </button>
                <?php 
                $settings = (new Election('', $_SESSION['election_id']))->getElectionSettings();
                if ($settings['results_visible']): 
                ?>
                <a href="?screen=results" class="btn btn-outline" style="text-decoration: none; margin-right: 10px;">
                    View Results
                </a>
                <?php endif; ?>
                <a href="?screen=login" class="btn btn-outline" style="text-decoration: none;">
                    Return to Home
                </a>
            </div>
        </div>
    </div>
</section>

<style>
/* Print styles for receipt */
@media print {
    body * {
        visibility: hidden;
    }
    #voteReceipt, #voteReceipt * {
        visibility: visible;
    }
    #voteReceipt {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>