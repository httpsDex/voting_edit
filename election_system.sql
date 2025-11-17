-- Create Database
CREATE DATABASE IF NOT EXISTS election_system;
USE election_system;

-- Table 1: Departments
CREATE TABLE departments (
    department_id INT PRIMARY KEY AUTO_INCREMENT,
    department_name VARCHAR(100) UNIQUE NOT NULL
);

-- Table 2: Elections
CREATE TABLE elections (
    election_id INT PRIMARY KEY AUTO_INCREMENT,
    election_name VARCHAR(200) NOT NULL,
    election_year YEAR NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    voting_open BOOLEAN DEFAULT FALSE,
    results_visible BOOLEAN DEFAULT FALSE,
    allow_vote_changes BOOLEAN DEFAULT FALSE
);

-- Table 3: Voter Eligibility Rules
CREATE TABLE voter_eligibility (
    eligibility_id INT PRIMARY KEY AUTO_INCREMENT,
    election_id INT NOT NULL,
    department_id INT NOT NULL,
    id_range_start VARCHAR(20) NOT NULL,
    id_range_end VARCHAR(20) NOT NULL,
    FOREIGN KEY (election_id) REFERENCES elections(election_id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE
);

-- Table 4: Positions
CREATE TABLE positions (
    position_id INT PRIMARY KEY AUTO_INCREMENT,
    position_name VARCHAR(100) NOT NULL,
    position_order INT NOT NULL,
    max_selections INT DEFAULT 1
);

-- Table 5: Teams
CREATE TABLE teams (
    team_id INT PRIMARY KEY AUTO_INCREMENT,
    team_color VARCHAR(20) NOT NULL
);

-- Table 6: Candidates (SIMPLIFIED - removed student_id foreign key)
CREATE TABLE candidates (
    candidate_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    election_id INT NOT NULL,
    position_id INT NOT NULL,
    team_id INT NOT NULL,
    bio TEXT,
    department_id INT NOT NULL,
    FOREIGN KEY (election_id) REFERENCES elections(election_id) ON DELETE CASCADE,
    FOREIGN KEY (position_id) REFERENCES positions(position_id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(team_id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE
);

-- Table 7: Voters (stores anonymous voter IDs)
CREATE TABLE voters (
    voter_id INT PRIMARY KEY AUTO_INCREMENT,
    voter_identifier VARCHAR(20) NOT NULL,
    election_id INT NOT NULL,
    department_id INT NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (election_id) REFERENCES elections(election_id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE,
    UNIQUE KEY unique_voter (voter_identifier, election_id)
);

-- Table 8: Votes (Anonymous)
CREATE TABLE votes (
    vote_id INT PRIMARY KEY AUTO_INCREMENT,
    election_id INT NOT NULL,
    position_id INT NOT NULL,
    candidate_id INT NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (election_id) REFERENCES elections(election_id) ON DELETE CASCADE,
    FOREIGN KEY (position_id) REFERENCES positions(position_id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(candidate_id) ON DELETE CASCADE
);

-- Table 9: Admin Users
CREATE TABLE admin_users (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Sample Departments
INSERT INTO departments (department_name) VALUES
('Computer Science'),
('Engineering'),
('Business Administration'),
('Arts and Humanities'),
('Natural Sciences'),
('Medicine');

-- Insert Sample Teams
INSERT INTO teams (team_color) VALUES
('white'),
('maroon');

-- Insert Sample Positions
INSERT INTO positions (position_name, position_order, max_selections) VALUES
('President', 1, 1),
('Vice President', 2, 1),
('Secretary', 3, 1),
('Treasurer', 4, 1),
('Auditor', 5, 1),
('Senator', 6, 4);

-- Insert Sample Election
INSERT INTO elections (election_name, election_year, start_date, end_date, voting_open, results_visible, allow_vote_changes) VALUES
('Student Council Election 2024', 2024, '2024-01-01 08:00:00', '2024-12-31 18:00:00', 1, 1, 0);

-- Insert Sample Admin (username: admin, password: admin123)
INSERT INTO admin_users (username, password_hash, full_name, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@university.edu');

-- Insert Sample Candidates (SIMPLIFIED - no student_id dependency)
INSERT INTO candidates (first_name, last_name, election_id, position_id, team_id, bio, department_id) VALUES
('Alex', 'Johnson', 1, 1, 1, 'Computer Science major with a focus on improving campus technology and student resources.', 1),
('Maria', 'Garcia', 1, 1, 2, 'Business Administration student advocating for more inclusive campus events and activities.', 3),
('Jordan', 'Smith', 1, 2, 1, 'Engineering student committed to sustainability initiatives and improved study spaces.', 2),
('Taylor', 'Williams', 1, 2, 2, 'Arts major focused on expanding creative opportunities and mental health resources.', 4),
('Casey', 'Brown', 1, 3, 1, 'Computer Science student with experience in communication and event organization.', 1),
('Riley', 'Davis', 1, 3, 2, 'Science major dedicated to transparent and efficient student governance.', 5),
('Morgan', 'Miller', 1, 4, 1, 'Engineering student with experience in budget management and fundraising.', 2),
('Jamie', 'Wilson', 1, 4, 2, 'Business major focused on financial transparency and responsible spending.', 3),
('Skyler', 'Martinez', 1, 5, 1, 'Arts student committed to accountability and thorough review processes.', 4),
('Avery', 'Anderson', 1, 5, 2, 'Science major focused on ensuring compliance and ethical governance.', 5),
('Jordan', 'Lee', 1, 6, 1, 'Computer Science representative', 1),
('Taylor', 'Kim', 1, 6, 1, 'Engineering representative', 2),
('Casey', 'Park', 1, 6, 1, 'Business representative', 3),
('Riley', 'Choi', 1, 6, 1, 'Arts representative', 4),
('Morgan', 'Chen', 1, 6, 2, 'Science representative', 5),
('Jamie', 'Rodriguez', 1, 6, 2, 'Multi-discipline representative', 1);

-- Insert Sample Voter Eligibility (Example: A22-0001 to A22-9999 for Computer Science can vote)
INSERT INTO voter_eligibility (election_id, department_id, id_range_start, id_range_end) VALUES
(1, 1, 'A22-0001', 'A22-9999'),
(1, 2, 'A22-0001', 'A22-9999'),
(1, 3, 'A22-0001', 'A22-9999'),
(1, 4, 'A22-0001', 'A22-9999'),
(1, 5, 'A22-0001', 'A22-9999');