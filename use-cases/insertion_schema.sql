START TRANSACTION

INSERT INTO users (username) VALUES
('juswa eviota'),
('bosh dave'),
('yudobshere mijares'),
('bosh dave'),
('tristan boonifacio');

INSERT INTO user_activity_logs (user_id, action) VALUES
(1, 'login'),
(1, 'viewed_profile'),
(2, 'login'),
(2, 'updated_settings');

INSERT INTO admin (username) VALUES
('admin'),
('moderator');

INSERT INTO sessions (user_id, token, expires_at) VALUES
(1, 'token_abc123xyz', DATE_ADD(NOW(), INTERVAL 7 DAY)),
(2, 'token_def456uvw', DATE_ADD(NOW(), INTERVAL 7 DAY));

INSERT INTO user_profiles (user_id, email, first_name, last_name) VALUES
(1, 'yudobs@example.com', 'yudobshere', 'mijares');


COMMIT;