CREATE DATABASE IF NOT EXISTS greenlife_wellness;

USE greenlife_wellness;

-- Recreating users table with only used columns
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `username` VARCHAR(100) UNIQUE NOT NULL,
    `fullname` VARCHAR(100) NOT NULL,
    `password` TEXT NOT NULL,
    `role` ENUM('client', 'therapist', 'admin') DEFAULT 'client',
    `profile_image` VARCHAR(255) NULL DEFAULT 'default_avatar.jpg',
    `phone` VARCHAR(20) NULL,
    `last_session` DATE NULL,
    `assigned_therapist_id` INT NULL,
    `progress` INT(3) DEFAULT 0,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`assigned_therapist_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

-- Inserting sample therapists
INSERT INTO `users` (`email`, `username`, `fullname`, `password`, `role`, `profile_image`) VALUES
('thehan@greenlife.lk', 'thehandesilva', 'Thehan De Silva', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'therapist', 'therapist1.jpg'),
('kiara@greenlife.lk', 'kiarajayawardena', 'Kiara Jayawardena', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'therapist', 'therapist2.jpg'),
('rayan@greenlife.lk', 'rayandias', 'Rayan Dias', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'therapist', 'therapist3.jpg'),
('tasha@greenlife.lk', 'tashaperera', 'Tasha Perera', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin.jpg');

-- Inserting test users for dashboard testing
INSERT INTO `users` (`email`, `username`, `fullname`, `password`, `role`, `profile_image`, `phone`) VALUES
('a@gmail.com', 'admin', 'Admin', '123', 'admin', 'admin.jpg', '+94 11 111 1111'),
('t@gmail.com', 'therapist', 'Therapist', '123', 'therapist', 'therapist.jpg', '+94 22 222 2222'),
('c@gmail.com', 'client', 'Client', '123', 'client', 'client.jpg', '+94 33 333 3333');

-- Inserting 5 sample clients with Sri Lankan names and usernames
INSERT INTO `users` (`email`, `username`, `fullname`, `password`, `role`, `profile_image`, `last_session`, `assigned_therapist_id`, `progress`) VALUES
('dhiwan@email.com', 'dhiwanlakshan', 'Dhiwan Lakshan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 'client1.jpg', '2024-07-10', 1, 75),
('aven@email.com', 'avenperera', 'Aven Perera', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 'client2.jpg', '2024-07-08', 2, 50),
('zinali@email.com', 'zinaliratnayake', 'Zinali Ratnayake', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 'client3.jpg', '2024-07-05', 3, 90),
('oshini@email.com', 'oshinifernando', 'Oshini Fernando', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 'client4.jpg', '2024-07-03', 1, 65),
('enaya@email.com', 'enayadesilva', 'Enaya De Silva', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 'client5.jpg', '2024-06-28', 2, 80);

-- Update test client to be assigned to test therapist
UPDATE `users` SET `assigned_therapist_id` = (SELECT id FROM users WHERE email = 't@gmail.com') WHERE email = 'c@gmail.com';

-- Recreating services table with only used columns
CREATE TABLE IF NOT EXISTS `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `summary` text NOT NULL,
  `benefits` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `booking_text` varchar(50) DEFAULT 'Book Session',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserting sample services
INSERT INTO `services` (`name`, `category`, `summary`, `image`, `benefits`, `booking_text`) VALUES
('Ayurvedic Therapy', 'Traditional', 'Ancient healing system using natural remedies and lifestyle interventions to restore balance.', 'ayurveda.jpg', 'Detoxifies and rejuvenates the body\nImproves digestion and metabolism\nReduces stress and promotes relaxation\nEnhances overall well-being', 'Book Session'),
('Yoga & Meditation', 'Mind-Body', 'Combination of physical postures, breathing techniques, and mindfulness practices.', 'yoga.jpg', 'Increases flexibility and strength\nReduces anxiety and depression\nImproves focus and mental clarity\nEnhances mind-body connection', 'Book Session'),
('Nutrition & Diet', 'Wellness', 'Personalized dietary plans and nutritional guidance to optimize health and wellbeing.', 'nutrition.jpg', 'Customized meal planning\nWeight management programs\nGut health optimization\nFood sensitivity testing\nSports nutrition guidance', 'Book Consultation'),
('Massage Therapy', 'Bodywork', 'Therapeutic manipulation of soft tissues to relieve tension and promote relaxation.', 'massage.jpg', 'Relieves muscle tension and pain\nImproves circulation\nReduces stress hormones\nEnhances flexibility\nPromotes better sleep', 'Book Session'),
('Aromatic Therapy', 'Holistic', 'Therapeutic use of plant essences to enhance physical and emotional wellbeing.', 'aroma.jpg', 'Custom essential oil blends\nMood enhancement\nRespiratory support\nSkin nourishment\nStress reduction techniques', 'Book Session'),
('Physiotherapy', 'Rehabilitation', 'Evidence-based techniques to restore movement and function affected by injury or illness.', 'physiotherapy.jpg', 'Pain management\nPost-surgical rehabilitation\nSports injury recovery\nPosture correction\nMobility improvement', 'Book Assessment');

-- Creating appointments table
CREATE TABLE IF NOT EXISTS `appointments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NOT NULL,
    `therapist_id` INT NOT NULL,
    `service_id` INT NOT NULL,
    `appointment_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `duration` INT NOT NULL DEFAULT 60,
    `status` ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`client_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE
);

-- Creating resources tables with only used columns
CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `summary` text NOT NULL,
  `content` longtext NOT NULL,
  `image` varchar(255) NOT NULL,
  `read_time` int(3) DEFAULT 5,
  `publish_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `youtube_id` varchar(20) NOT NULL,
  `youtube_url` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT 'Wellness',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `health_tips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(100) NOT NULL,
  `image` varchar(255) NOT NULL,
  `duration` varchar(50) DEFAULT 'Daily',
  `benefits` varchar(255) DEFAULT 'Improves health',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserting sample articles
INSERT INTO `articles` (`title`, `category`, `summary`, `content`, `image`, `read_time`, `publish_date`) VALUES
('Seasonal Detox: Cleanse Your Body Naturally', 'Nutrition', 'Our comprehensive guide to gentle seasonal detoxification using whole foods and herbal remedies that support your body\'s natural cleansing processes.', '<h4>Understanding Seasonal Detoxification</h4><p>Seasonal detoxification is a natural process that helps your body eliminate accumulated toxins and restore balance. Our bodies naturally crave cleansing foods during seasonal transitions, and by following these gentle practices, you can support your body\'s innate healing abilities.</p><h4>Key Benefits of Seasonal Detox</h4><ul><li><strong>Improved Digestion:</strong> Gentle cleansing supports healthy gut function</li><li><strong>Enhanced Energy:</strong> Remove toxins that cause fatigue and sluggishness</li><li><strong>Better Immunity:</strong> Strengthen your body\'s natural defense systems</li><li><strong>Mental Clarity:</strong> Clear mind fog and improve focus</li><li><strong>Radiant Skin:</strong> Natural glow from internal cleansing</li></ul><h4>Natural Detox Methods</h4><p>Start your detox journey with these gentle, natural approaches:</p><ol><li><strong>Hydration:</strong> Drink 8-10 glasses of pure water daily</li><li><strong>Herbal Teas:</strong> Incorporate dandelion, ginger, and green tea</li><li><strong>Whole Foods:</strong> Focus on organic fruits, vegetables, and whole grains</li><li><strong>Mindful Eating:</strong> Chew slowly and eat in a relaxed environment</li><li><strong>Gentle Exercise:</strong> Light walking, yoga, or stretching</li></ol><blockquote>"The best detox is the one that feels nourishing, not depriving. Listen to your body and honor its natural rhythms."</blockquote>', 'detox-article.jpg', 8, '2025-06-10'),
('Transform Your Mornings With These Yoga Sequences', 'Yoga', 'Learn how specific yoga poses practiced in the morning can boost your energy, improve flexibility, and set a positive tone for your entire day.', '<h4>The Power of Morning Yoga</h4><p>Morning yoga is more than just exercise—it\'s a sacred ritual that connects mind, body, and spirit. By dedicating just 15-20 minutes each morning to these gentle sequences, you can transform your entire day and cultivate lasting wellness habits.</p><h4>Benefits of Morning Practice</h4><ul><li><strong>Increased Energy:</strong> Wake up naturally without caffeine dependency</li><li><strong>Mental Clarity:</strong> Clear morning fog and improve focus</li><li><strong>Stress Reduction:</strong> Start your day with calm and intention</li><li><strong>Better Posture:</strong> Strengthen core and improve alignment</li><li><strong>Emotional Balance:</strong> Cultivate inner peace and resilience</li></ul><h4>Essential Morning Sequence</h4><p>Follow this gentle sequence to awaken your body and mind:</p><ol><li><strong>Child\'s Pose (2 minutes):</strong> Gentle stretch for back and hips</li><li><strong>Cat-Cow Stretches (3 minutes):</strong> Warm up spine and core</li><li><strong>Sun Salutations (5 minutes):</strong> Energize and build heat</li><li><strong>Standing Poses (5 minutes):</strong> Build strength and balance</li><li><strong>Seated Meditation (5 minutes):</strong> Center and ground yourself</li></ol><blockquote>"Your morning practice sets the tone for your entire day. Make it sacred, make it yours."</blockquote>', 'yoga-article.jpg', 7, '2025-06-15'),
('5 Ayurvedic Tips for Restful Sleep', 'Ayurveda', 'Discover how ancient Ayurvedic practices can help you achieve deeper, more restorative sleep naturally without relying on medication.', '<h4>Ayurvedic Sleep Wisdom</h4><p>According to Ayurveda, sleep is one of the three pillars of health, alongside diet and lifestyle. Quality sleep is essential for physical repair, mental clarity, and emotional balance. These ancient practices can help you create the perfect conditions for deep, restorative sleep.</p><h4>The 5 Ayurvedic Sleep Principles</h4><ol><li><strong>Establish a Consistent Sleep Schedule:</strong> Go to bed and wake up at the same time daily, even on weekends. This aligns your body with natural circadian rhythms.</li><li><strong>Create a Calming Evening Routine:</strong> Begin winding down 2-3 hours before bed with gentle activities like reading, meditation, or warm baths.</li><li><strong>Optimize Your Sleep Environment:</strong> Keep your bedroom cool, dark, and quiet. Use calming colors and natural materials.</li><li><strong>Mindful Evening Nutrition:</strong> Eat a light dinner 2-3 hours before bed and avoid stimulating foods and drinks.</li><li><strong>Practice Relaxation Techniques:</strong> Incorporate breathing exercises, gentle yoga, or meditation before sleep.</li></ol><h4>Natural Sleep Remedies</h4><ul><li><strong>Warm Milk with Spices:</strong> Add a pinch of nutmeg, cardamom, or turmeric</li><li><strong>Herbal Teas:</strong> Chamomile, valerian root, or passionflower</li><li><strong>Essential Oils:</strong> Lavender, sandalwood, or jasmine for aromatherapy</li><li><strong>Self-Massage:</strong> Gentle oil massage (abhyanga) before bed</li><li><strong>Breathing Exercises:</strong> 4-7-8 breathing technique</li></ul><blockquote>"Sleep is the best meditation. When you sleep well, you wake up with clarity, energy, and purpose."</blockquote>', 'sleep-article.jpg', 5, '2025-06-20');

-- Inserting sample videos
INSERT INTO `videos` (`title`, `description`, `youtube_id`, `youtube_url`, `category`) VALUES
('Morning Yoga Routine', '15-minute yoga sequence to start your day right.', 'r7xsYgTeM2Q', 'https://youtu.be/r7xsYgTeM2Q?si=096EXceBFnDQeLag', 'Yoga'),
('Healthy Meal Prep', 'Learn how to prepare nutritious meals for the week.', '1EpfvEz-91Q', 'https://youtu.be/1EpfvEz-91Q?si=n1CQZnW7zW-kMEpR', 'Nutrition');

-- Inserting sample health tips
INSERT INTO `health_tips` (`title`, `description`, `category`, `image`, `duration`, `benefits`) VALUES
('Stay Hydrated Throughout the Day', 'Drink at least 8-10 glasses of water daily. Proper hydration improves digestion, skin health, and energy levels.', 'Hydration', 'hydration-tip.jpg', 'Daily', 'Improves metabolism'),
('Quality Sleep Routine', 'Aim for 7-8 hours of uninterrupted sleep. Establish a regular sleep schedule and create a calming bedtime routine.', 'Sleep', 'sleep-tip.jpg', 'Nightly', 'Boosts immunity'),
('30-Minute Daily Walk', 'Walking improves cardiovascular health, strengthens bones, and reduces stress. Try a brisk pace for maximum benefits.', 'Exercise', 'walking-tip.jpg', '30 mins/day', 'Heart health'),
('Morning Mindfulness', 'Start your day with 10 minutes of meditation. Focus on your breath to reduce stress and increase mental clarity.', 'Mindfulness', 'meditation-tip.jpg', '10 mins/day', 'Reduces anxiety'),
('Colorful Plate Principle', 'Include 5 different colored fruits/vegetables in your meals daily for a variety of nutrients and antioxidants.', 'Nutrition', 'nutrition-tip.jpg', 'Each meal', 'Nutrient-rich');

-- Creating inquiries table for client inquiries and therapist responses
CREATE TABLE IF NOT EXISTS `inquiries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NULL,
    `client_name` VARCHAR(100) NOT NULL,
    `client_email` VARCHAR(100) NOT NULL,
    `client_phone` VARCHAR(20) NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `category` ENUM('General Inquiry', 'Service Question', 'Appointment', 'Emergency') DEFAULT 'General Inquiry',
    `status` ENUM('open', 'pending', 'resolved', 'urgent') DEFAULT 'open',
    `assigned_therapist_id` INT NULL,
    `response` TEXT NULL,
    `response_date` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`client_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`assigned_therapist_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

-- Creating sessions table for tracking therapy sessions
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `appointment_id` INT NOT NULL,
    `therapist_id` INT NOT NULL,
    `client_id` INT NOT NULL,
    `service_id` INT NOT NULL,
    `session_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `duration` INT NOT NULL DEFAULT 60,
    `status` ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    `notes` TEXT NULL,
    `client_feedback` TEXT NULL,
    `therapist_notes` TEXT NULL,
    `progress_rating` INT(3) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`client_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE
);

-- Creating client_progress table for tracking client improvements
CREATE TABLE IF NOT EXISTS `client_progress` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NOT NULL,
    `therapist_id` INT NOT NULL,
    `session_id` INT NULL,
    `progress_date` DATE NOT NULL,
    `progress_percentage` INT(3) NOT NULL DEFAULT 0,
    `notes` TEXT NULL,
    `goals_achieved` TEXT NULL,
    `next_goals` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`client_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`session_id`) REFERENCES `sessions`(`id`) ON DELETE SET NULL
);

-- Creating notifications table for cross-dashboard communication
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('appointment', 'inquiry', 'progress', 'system', 'emergency') DEFAULT 'system',
    `is_read` BOOLEAN DEFAULT FALSE,
    `related_id` INT NULL,
    `related_type` VARCHAR(50) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- Creating dedicated therapists table with all details
CREATE TABLE IF NOT EXISTS `therapists` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `fullname` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NULL,
    `profile_image` VARCHAR(255) DEFAULT 'therapist1.jpg',
    `specialization` TEXT NOT NULL,
    `services` TEXT NOT NULL,
    `experience_years` INT DEFAULT 0,
    `qualifications` TEXT NULL,
    `bio` TEXT NULL,
    `hourly_rate` DECIMAL(10,2) DEFAULT 0.00,
    `availability` TEXT NOT NULL,
    `working_days` VARCHAR(100) NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- Inserting therapist data with all details including availability
INSERT INTO `therapists` (`user_id`, `fullname`, `email`, `phone`, `profile_image`, `specialization`, `services`, `experience_years`, `qualifications`, `bio`, `hourly_rate`, `availability`, `working_days`, `start_time`, `end_time`) VALUES
(1, 'Thehani De Silva', 'thehani@greenlife.lk', '+94 71 234 5678', 'therapist1.jpg', 'Traditional Healing & Nutrition', 'Ayurvedic Therapy, Nutrition & Diet', 8, 'BSc Ayurvedic Medicine, Certified Nutritionist', 'Thehan specializes in traditional Ayurvedic healing and personalized nutrition plans. With 8 years of experience, he combines ancient wisdom with modern nutritional science to help clients achieve optimal health and wellness.', 75.00, 'Available for consultations and therapy sessions', 'Monday - Friday', '09:00:00', '17:00:00'),
(2, 'Kiara Jayawardena', 'kiara@greenlife.lk', '+94 77 345 6789', 'therapist2.jpg', 'Holistic Wellness & Mind-Body Practices', 'Aromatic Therapy, Yoga & Meditation', 6, 'Certified Aromatherapist, 500hr Yoga Teacher Training', 'Kiara is passionate about holistic wellness and mind-body connection. She combines aromatherapy with yoga and meditation to create transformative healing experiences for her clients.', 65.00, 'Available for wellness sessions and classes', 'Monday - Saturday', '08:00:00', '16:00:00'),
(3, 'Rayan Dias', 'rayan@greenlife.lk', '+94 76 456 7890', 'therapist3.jpg', 'Physical Therapy & Bodywork', 'Massage Therapy, Physiotherapy', 10, 'BSc Physiotherapy, Licensed Massage Therapist', 'Rayan is an experienced physiotherapist and massage therapist with 10 years of practice. He specializes in pain management, rehabilitation, and therapeutic bodywork techniques.', 80.00, 'Available for therapy and rehabilitation sessions', 'Monday - Friday', '10:00:00', '18:00:00');

-- Inserting sample inquiries
INSERT INTO `inquiries` (`client_name`, `client_email`, `client_phone`, `subject`, `message`, `category`, `status`, `assigned_therapist_id`) VALUES
('Dhiwan Lakshan', 'dhiwan@email.com', '+94 71 234 5678', 'Yoga Session Inquiry', 'Hi, I\'m interested in starting yoga sessions. I\'m a beginner and would like to know more about your beginner-friendly classes and pricing. Also, what should I bring for my first session?', 'Service Question', 'open', 1),
('Aven Perera', 'aven@email.com', '+94 77 345 6789', 'Massage Therapy Question', 'I\'ve been experiencing back pain and heard that massage therapy might help. Could you tell me more about your massage therapy services and if you offer deep tissue massage? What\'s the typical duration and cost?', 'Service Question', 'pending', 2),
('Zinali Ratnayake', 'zinali@email.com', '+94 76 456 7890', 'Appointment Reschedule', 'I\'d like to reschedule my appointment from tomorrow to next week. Is there any availability on Tuesday or Wednesday afternoon? Also, I wanted to ask about the meditation session you mentioned.', 'Appointment', 'resolved', 3),
('Oshini Fernando', 'oshini@email.com', '+94 70 567 8901', 'Emergency Help Needed', 'I\'m having severe anxiety and need immediate help. I\'ve heard about your stress management sessions. Can you please call me as soon as possible? I\'m really struggling right now.', 'Emergency', 'urgent', 1);

-- Inserting test inquiries for test therapist
INSERT INTO `inquiries` (`client_name`, `client_email`, `client_phone`, `subject`, `message`, `category`, `status`, `assigned_therapist_id`) VALUES
('Test Client', 'c@gmail.com', '+94 33 333 3333', 'Test Inquiry', 'This is a test inquiry to check if the dashboard is working properly. Can you please respond to this?', 'General Inquiry', 'open', (SELECT id FROM users WHERE email = 't@gmail.com')),
('John Doe', 'john.doe@email.com', '+94 44 444 4444', 'Yoga Classes', 'I would like to know more about your yoga classes and pricing. Are there any beginner-friendly sessions?', 'Service Question', 'pending', (SELECT id FROM users WHERE email = 't@gmail.com'));

-- Inserting sample appointments
INSERT INTO `appointments` (`client_id`, `therapist_id`, `service_id`, `appointment_date`, `start_time`, `end_time`, `status`, `notes`) VALUES
(6, 1, 2, '2025-01-20', '10:00:00', '11:00:00', 'confirmed', 'First yoga session for beginner'),
(7, 2, 4, '2025-01-21', '14:00:00', '15:00:00', 'confirmed', 'Deep tissue massage for back pain'),
(8, 3, 2, '2025-01-22', '09:00:00', '10:00:00', 'pending', 'Meditation session'),
(9, 1, 1, '2025-01-23', '11:00:00', '12:00:00', 'confirmed', 'Ayurvedic consultation'),
(10, 2, 5, '2025-01-24', '15:00:00', '16:00:00', 'confirmed', 'Aromatherapy session');

-- Inserting test appointments for test users
INSERT INTO `appointments` (`client_id`, `therapist_id`, `service_id`, `appointment_date`, `start_time`, `end_time`, `status`, `notes`) VALUES
((SELECT id FROM users WHERE email = 'c@gmail.com'), (SELECT id FROM users WHERE email = 't@gmail.com'), 2, '2025-01-25', '10:00:00', '11:00:00', 'confirmed', 'Test yoga session'),
((SELECT id FROM users WHERE email = 'c@gmail.com'), (SELECT id FROM users WHERE email = 't@gmail.com'), 1, '2025-01-26', '14:00:00', '15:00:00', 'pending', 'Test ayurvedic session');

-- Inserting sample sessions
INSERT INTO `sessions` (`appointment_id`, `therapist_id`, `client_id`, `service_id`, `session_date`, `start_time`, `end_time`, `status`, `therapist_notes`) VALUES
(1, 1, 6, 2, '2025-01-15', '10:00:00', '11:00:00', 'completed', 'Client showed good flexibility. Recommended daily practice.'),
(2, 2, 7, 4, '2025-01-16', '14:00:00', '15:00:00', 'completed', 'Back pain significantly reduced. Scheduled follow-up.'),
(3, 3, 8, 2, '2025-01-17', '09:00:00', '10:00:00', 'completed', 'Client responded well to breathing techniques.');

-- Inserting test sessions for test users
INSERT INTO `sessions` (`appointment_id`, `therapist_id`, `client_id`, `service_id`, `session_date`, `start_time`, `end_time`, `status`, `therapist_notes`) VALUES
((SELECT id FROM appointments WHERE client_id = (SELECT id FROM users WHERE email = 'c@gmail.com') LIMIT 1), 
 (SELECT id FROM users WHERE email = 't@gmail.com'), 
 (SELECT id FROM users WHERE email = 'c@gmail.com'), 
 2, '2025-01-20', '10:00:00', '11:00:00', 'completed', 'Test session completed successfully');

-- Inserting sample client progress
INSERT INTO `client_progress` (`client_id`, `therapist_id`, `session_id`, `progress_date`, `progress_percentage`, `notes`, `goals_achieved`) VALUES
(6, 1, 1, '2025-01-15', 75, 'Client making excellent progress with yoga practice', 'Improved flexibility, better breathing technique'),
(7, 2, 2, '2025-01-16', 50, 'Back pain management progressing well', 'Reduced pain levels, better posture'),
(8, 3, 3, '2025-01-17', 90, 'Client showing remarkable improvement in meditation', 'Achieved deep relaxation, reduced anxiety');

-- Inserting test progress for test client
INSERT INTO `client_progress` (`client_id`, `therapist_id`, `progress_date`, `progress_percentage`, `notes`, `goals_achieved`) VALUES
((SELECT id FROM users WHERE email = 'c@gmail.com'), (SELECT id FROM users WHERE email = 't@gmail.com'), '2025-01-20', 60, 'Test client making good progress', 'Improved flexibility, better breathing');

-- Inserting sample notifications
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `related_id`, `related_type`) VALUES
(1, 'New Client Inquiry', 'Dhiwan Lakshan has submitted an inquiry about yoga sessions', 'inquiry', 1, 'inquiry'),
(2, 'Appointment Confirmed', 'Your appointment with Aven Perera has been confirmed for tomorrow', 'appointment', 2, 'appointment'),
(3, 'Client Progress Update', 'Zinali Ratnayake has shown significant improvement in meditation', 'progress', 3, 'session'),
(6, 'Session Reminder', 'Your yoga session with Thehan De Silva is scheduled for tomorrow at 10:00 AM', 'appointment', 1, 'appointment');

-- Inserting test notifications for test users
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `related_id`, `related_type`) VALUES
((SELECT id FROM users WHERE email = 't@gmail.com'), 'New Test Inquiry', 'Test client has submitted an inquiry', 'inquiry', 1, 'inquiry'),
((SELECT id FROM users WHERE email = 'c@gmail.com'), 'Test Appointment', 'Your test appointment has been scheduled', 'appointment', 1, 'appointment');

-- Creating therapist_services table to link therapists with services they offer
CREATE TABLE IF NOT EXISTS `therapist_services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `therapist_id` INT NOT NULL,
    `service_id` INT NOT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_therapist_service` (`therapist_id`, `service_id`)
);

-- Inserting sample therapist-service relationships
INSERT INTO `therapist_services` (`therapist_id`, `service_id`) VALUES
(1, 1), -- Thehan De Silva - Ayurvedic Therapy
(1, 2), -- Thehan De Silva - Yoga & Meditation
(2, 2), -- Kiara Jayawardena - Yoga & Meditation
(2, 3), -- Kiara Jayawardena - Nutrition & Diet
(3, 4), -- Rayan Dias - Massage Therapy
(3, 5), -- Rayan Dias - Aromatic Therapy
(1, 6), -- Thehan De Silva - Physiotherapy
(2, 6); -- Kiara Jayawardena - Physiotherapy

-- Creating therapist_availability table to store therapist working schedules
CREATE TABLE IF NOT EXISTS `therapist_availability` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `therapist_id` INT NOT NULL,
    `day_of_week` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `is_available` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_therapist_day` (`therapist_id`, `day_of_week`)
);

-- Inserting sample therapist availability (Monday to Friday, 9 AM to 5 PM)
INSERT INTO `therapist_availability` (`therapist_id`, `day_of_week`, `start_time`, `end_time`) VALUES
-- Thehan De Silva availability
(1, 'Monday', '09:00:00', '17:00:00'),
(1, 'Tuesday', '09:00:00', '17:00:00'),
(1, 'Wednesday', '09:00:00', '17:00:00'),
(1, 'Thursday', '09:00:00', '17:00:00'),
(1, 'Friday', '09:00:00', '17:00:00'),

-- Kiara Jayawardena availability
(2, 'Monday', '09:00:00', '17:00:00'),
(2, 'Tuesday', '09:00:00', '17:00:00'),
(2, 'Wednesday', '09:00:00', '17:00:00'),
(2, 'Thursday', '09:00:00', '17:00:00'),
(2, 'Friday', '09:00:00', '17:00:00'),

-- Rayan Dias availability
(3, 'Monday', '09:00:00', '17:00:00'),
(3, 'Tuesday', '09:00:00', '17:00:00'),
(3, 'Wednesday', '09:00:00', '17:00:00'),
(3, 'Thursday', '09:00:00', '17:00:00'),
(3, 'Friday', '09:00:00', '17:00:00');