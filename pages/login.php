<?php
$election_obj = new Election();
$departments = $election_obj->getAllDepartments();
?>
<section id="loginScreen" class="screen active">
    <div class="login-container">
        <div class="login-header">
            <h1>Voter Authentication</h1>
            <p>Please enter your voter ID and department to continue</p>
        </div>
        <div class="card">
            <form action="process.php" method="POST">
                <div class="form-group">
                    <label for="voter_id">Voter ID</label>
                    <input type="text" id="voter_id" name="voter_id" placeholder="e.g., A22-0001" required>
                    <small style="color: #666; font-size: 13px;">Format: A[Year]-[Number] (e.g., A22-0001)</small>
                    <?php if (isset($_SESSION['login_error'])): ?>
                        <div class="error-message"><?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="department_id">Department</label>
                    <select id="department_id" name="department_id" required>
                        <option value="">Select your department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['department_id']; ?>"><?php echo $dept['department_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="login" class="btn" style="width: 100%;">Continue to Verification</button>
            </form>
            <div class="admin-toggle">
                <a href="#" id="adminToggle">Administrator Access</a>
            </div>
        </div>
    </div>
</section>