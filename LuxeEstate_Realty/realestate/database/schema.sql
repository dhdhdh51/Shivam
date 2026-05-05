-- =====================================================
-- Real Estate CMS - Database Schema
-- Encoding: UTF-8 | Engine: InnoDB
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+05:30";

CREATE DATABASE IF NOT EXISTS `realestate_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `realestate_db`;

-- =====================================================
-- Table: users (Admin Authentication)
-- =====================================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin') DEFAULT 'admin',
  `avatar` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin: admin@realestate.com / Admin@123
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Super Admin', 'admin@realestate.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

-- =====================================================
-- Table: settings (Site Configuration)
-- =====================================================
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('site_name', 'LuxeEstate Realty', 'general'),
('site_tagline', 'Your Dream Home Awaits', 'general'),
('site_logo', '', 'general'),
('site_favicon', '', 'general'),
('contact_phone', '+91 98765 43210', 'contact'),
('contact_phone2', '+91 87654 32109', 'contact'),
('contact_email', 'info@luxeestate.com', 'contact'),
('contact_address', '123 Gold Tower, MG Road, Bangalore - 560001', 'contact'),
('whatsapp_number', '919876543210', 'contact'),
('facebook_url', 'https://facebook.com/', 'social'),
('instagram_url', 'https://instagram.com/', 'social'),
('youtube_url', 'https://youtube.com/', 'social'),
('linkedin_url', 'https://linkedin.com/', 'social'),
('twitter_url', 'https://twitter.com/', 'social'),
('meta_title', 'LuxeEstate Realty - Premium Properties', 'seo'),
('meta_description', 'Find your dream home with LuxeEstate. Premium residential and commercial properties across India.', 'seo'),
('meta_keywords', 'real estate, properties, buy home, luxury apartments, villa', 'seo'),
('google_analytics', '', 'seo'),
('section_hero', '1', 'sections'),
('section_featured', '1', 'sections'),
('section_stats', '1', 'sections'),
('section_services', '1', 'sections'),
('section_testimonials', '1', 'sections'),
('section_team', '1', 'sections'),
('section_blog', '1', 'sections'),
('section_cta', '1', 'sections'),
('hero_title', 'Find Your Perfect Home', 'hero'),
('hero_subtitle', 'Premium properties curated for the discerning buyer. Experience luxury living redefined.', 'hero'),
('hero_bg_image', '', 'hero'),
('stats_properties', '500', 'stats'),
('stats_clients', '1200', 'stats'),
('stats_cities', '25', 'stats'),
('stats_years', '15', 'stats'),
('popup_enabled', '1', 'popup'),
('popup_delay', '5000', 'popup'),
('popup_title', 'Get Free Consultation', 'popup'),
('footer_about', 'LuxeEstate has been redefining luxury real estate since 2009. We connect discerning clients with their perfect properties across India.', 'footer'),
('currency_symbol', '₹', 'general'),
('properties_per_page', '9', 'general');

-- =====================================================
-- Table: properties
-- =====================================================
CREATE TABLE `properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL UNIQUE,
  `price` decimal(15,2) NOT NULL DEFAULT 0,
  `price_label` varchar(50) DEFAULT NULL COMMENT 'e.g. Onwards, Negotiable',
  `location` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT 'Karnataka',
  `pincode` varchar(10) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `type` enum('apartment','villa','plot','commercial','penthouse','studio','duplex','farmhouse') NOT NULL DEFAULT 'apartment',
  `bhk` tinyint(3) DEFAULT NULL,
  `bathrooms` tinyint(3) DEFAULT NULL,
  `area_sqft` int(11) DEFAULT NULL,
  `area_sqyd` int(11) DEFAULT NULL,
  `floor` tinyint(3) DEFAULT NULL,
  `total_floors` tinyint(3) DEFAULT NULL,
  `parking` tinyint(3) DEFAULT 0,
  `furnishing` enum('furnished','semi-furnished','unfurnished') DEFAULT 'unfurnished',
  `facing` enum('North','South','East','West','North-East','North-West','South-East','South-West') DEFAULT NULL,
  `possession` enum('ready','under-construction','pre-launch') DEFAULT 'ready',
  `possession_date` date DEFAULT NULL,
  `description` longtext NOT NULL,
  `amenities` json DEFAULT NULL,
  `nearby` json DEFAULT NULL COMMENT 'Schools, Hospitals, Metro etc.',
  `rera_number` varchar(100) DEFAULT NULL,
  `builder` varchar(150) DEFAULT NULL,
  `project_name` varchar(150) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `virtual_tour` varchar(500) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `trending` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive','sold') DEFAULT 'active',
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_status` (`status`),
  KEY `idx_featured` (`featured`),
  KEY `idx_city` (`city`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: property_images
-- =====================================================
CREATE TABLE `property_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_pi_property` (`property_id`),
  CONSTRAINT `fk_pi_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: leads
-- =====================================================
CREATE TABLE `leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `budget` varchar(100) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `property_name` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `source` varchar(50) DEFAULT 'website' COMMENT 'website, popup, whatsapp, call',
  `status` enum('new','contacted','qualified','closed','lost') DEFAULT 'new',
  `tag` enum('hot','warm','cold') DEFAULT 'warm',
  `notes` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `utm_source` varchar(100) DEFAULT NULL,
  `utm_medium` varchar(100) DEFAULT NULL,
  `utm_campaign` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_lead_property` (`property_id`),
  CONSTRAINT `fk_lead_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: blogs
-- =====================================================
CREATE TABLE `blogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL UNIQUE,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(500) DEFAULT NULL,
  `category` varchar(100) DEFAULT 'Real Estate',
  `tags` varchar(500) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `status` enum('published','draft') DEFAULT 'draft',
  `featured` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_blog_slug` (`slug`),
  KEY `fk_blog_author` (`author_id`),
  CONSTRAINT `fk_blog_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: testimonials
-- =====================================================
CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `designation` varchar(150) DEFAULT NULL,
  `review` text NOT NULL,
  `rating` tinyint(1) DEFAULT 5,
  `image` varchar(500) DEFAULT NULL,
  `property_bought` varchar(255) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `testimonials` (`name`, `designation`, `review`, `rating`, `property_bought`, `featured`) VALUES
('Rajesh Sharma', 'Software Engineer, Infosys', 'LuxeEstate made my dream of owning a premium apartment a reality. Their team was incredibly professional and guided me through every step of the process. Highly recommended!', 5, '3 BHK Apartment, Whitefield', 1),
('Priya Mehta', 'Business Owner', 'Exceptional service and a stunning portfolio of properties. Found exactly what I was looking for within my budget. The entire process was smooth and transparent.', 5, 'Villa, Sarjapur Road', 1),
('Vikram Nair', 'Doctor, Apollo Hospitals', 'I was skeptical at first, but LuxeEstate proved to be the most reliable real estate partner. Their knowledge of the market is unmatched. Bought a beautiful penthouse!', 5, 'Penthouse, Indiranagar', 1),
('Sunita Kapoor', 'HR Manager, TCS', 'The team at LuxeEstate went above and beyond. They understood our requirements perfectly and showed us properties that matched our lifestyle. Could not be happier!', 4, '2 BHK Apartment, HSR Layout', 1),
('Arjun Reddy', 'Entrepreneur', 'Invested in a commercial property through LuxeEstate and the ROI has been excellent. Their market insights and analysis are top-notch. Will definitely work with them again.', 5, 'Commercial Space, Koramangala', 1),
('Meera Singh', 'Teacher', 'As a first-time home buyer, I was nervous about the process. LuxeEstate made it so easy and stress-free. Got a beautiful home at a great price!', 5, '2 BHK Flat, Electronic City', 1);

-- =====================================================
-- Table: team_members
-- =====================================================
CREATE TABLE `team_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `role` varchar(150) NOT NULL,
  `bio` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `experience_years` tinyint(3) DEFAULT NULL,
  `properties_sold` int(11) DEFAULT 0,
  `specialization` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `team_members` (`name`, `role`, `bio`, `experience_years`, `properties_sold`, `specialization`) VALUES
('Aryan Kapoor', 'Founder & CEO', 'With 20+ years in premium real estate, Aryan has redefined luxury living standards across India.', 20, 500, 'Luxury Residential & Commercial'),
('Nisha Malhotra', 'Head of Sales', 'Nisha brings 12 years of expertise in high-value residential transactions and client relationships.', 12, 350, 'Premium Apartments & Villas'),
('Rahul Verma', 'Senior Property Consultant', 'Specializing in investment properties with a keen eye for high-ROI opportunities.', 8, 220, 'Investment Properties & Plots'),
('Divya Iyer', 'Client Relations Manager', 'Ensuring every client journey is seamless, from first inquiry to final possession.', 6, 180, 'Customer Experience & After Sales');

-- =====================================================
-- Table: property_enquiries (Quick Enquiry via Property Page)
-- =====================================================
CREATE TABLE `quick_enquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: csrf_tokens
-- =====================================================
CREATE TABLE `csrf_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL UNIQUE,
  `session_id` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Sample Properties Data
-- =====================================================
INSERT INTO `properties` (`title`, `slug`, `price`, `price_label`, `location`, `city`, `state`, `type`, `bhk`, `bathrooms`, `area_sqft`, `floor`, `total_floors`, `parking`, `furnishing`, `possession`, `description`, `amenities`, `featured`, `trending`, `status`) VALUES
('Luxurious 3 BHK Apartment in Whitefield', '3-bhk-apartment-whitefield', 8500000, 'Onwards', 'Whitefield, Bangalore', 'Bangalore', 'Karnataka', 'apartment', 3, 3, 1850, 12, 24, 2, 'semi-furnished', 'ready', 'Experience unmatched luxury in this stunning 3 BHK apartment situated in the heart of Whitefield. Boasting panoramic city views, premium fixtures, and world-class amenities, this home redefines contemporary living. The spacious living areas are flooded with natural light through floor-to-ceiling windows.', '["Swimming Pool","Gymnasium","24/7 Security","Clubhouse","Power Backup","Lift","Parking","Garden","Jogging Track","Indoor Games","Party Hall","Kids Play Area"]', 1, 1, 'active'),

('Premium Villa in Sarjapur Road', 'premium-villa-sarjapur-road', 25000000, 'Negotiable', 'Sarjapur Road, Bangalore', 'Bangalore', 'Karnataka', 'villa', 4, 5, 4500, NULL, 2, 3, 'furnished', 'ready', 'A magnificent 4 BHK villa that embodies the perfect blend of luxury and comfort. Nestled in a serene gated community, this stunning property features private pool, landscaped gardens, and the finest imported fittings throughout. An epitome of gracious living.', '["Private Pool","Home Theatre","Modular Kitchen","Smart Home","Solar Power","Servant Quarters","Garden","Basement Parking","Security","Club Access"]', 1, 1, 'active'),

('Sky Penthouse - Indiranagar', 'sky-penthouse-indiranagar', 45000000, NULL, 'Indiranagar, Bangalore', 'Bangalore', 'Karnataka', 'penthouse', 4, 4, 6000, 24, 24, 4, 'furnished', 'ready', 'The pinnacle of luxury living in Bangalore. This exclusive penthouse offers 360-degree panoramic views of the cityscape. Featuring a private terrace, jacuzzi, home automation, and private elevator access. Only for the most discerning of buyers.', '["Private Terrace","Jacuzzi","Private Elevator","Home Automation","Concierge Service","Gymnasium","Swimming Pool","Helipad Access","Wine Cellar","Private Bar"]', 1, 0, 'active'),

('2 BHK Modern Apartment - HSR Layout', '2-bhk-apartment-hsr-layout', 6500000, 'Onwards', 'HSR Layout, Bangalore', 'Bangalore', 'Karnataka', 'apartment', 2, 2, 1200, 7, 15, 1, 'semi-furnished', 'ready', 'A beautifully designed 2 BHK apartment in the posh locality of HSR Layout. This contemporary home features modular kitchen, premium vitrified flooring, and access to a host of lifestyle amenities. Perfect for young professionals and small families.', '["Gymnasium","Swimming Pool","Power Backup","Parking","Security","Clubhouse","Garden","Children Play Area"]', 0, 1, 'active'),

('Commercial Space - Koramangala', 'commercial-space-koramangala', 15000000, NULL, 'Koramangala 6th Block, Bangalore', 'Bangalore', 'Karnataka', 'commercial', NULL, 2, 2500, 3, 10, 4, 'unfurnished', 'ready', 'Prime commercial space in the bustling tech hub of Koramangala. Ideal for IT offices, co-working spaces, or retail showrooms. Excellent footfall, surrounded by premium restaurants and tech companies. High rental yield investment.', '["Power Backup","Central AC","Parking","Security","Fire Safety","High-Speed Internet","Server Room","Conference Hall"]', 1, 0, 'active'),

('4 BHK Independent House - JP Nagar', '4-bhk-house-jp-nagar', 18000000, 'Negotiable', 'JP Nagar Phase 2, Bangalore', 'Bangalore', 'Karnataka', 'villa', 4, 4, 3800, NULL, 3, 2, 'semi-furnished', 'ready', 'Elegant independent house spread over a generous plot in the sought-after JP Nagar Phase 2. Features include a spacious living area, dedicated study room, utility area, and beautiful garden. A rare find in this premium locality.', '["Garden","Parking","Power Backup","Security","Pooja Room","Terrace","Storage Room","Solar Water Heater"]', 0, 1, 'active'),

('Studio Apartment - Electronic City', 'studio-apartment-electronic-city', 3200000, 'Onwards', 'Electronic City Phase 1', 'Bangalore', 'Karnataka', 'studio', 1, 1, 650, 5, 18, 1, 'furnished', 'ready', 'Smart and stylish fully-furnished studio apartment perfect for IT professionals. Comes with modular kitchen, loft storage, working desk setup, and access to premium amenities. Walking distance from major tech parks.', '["Gymnasium","Cafeteria","Laundry","Power Backup","Security","Swimming Pool","Co-working Space","EV Charging"]', 0, 1, 'active'),

('Luxury Duplex - Jayanagar', 'luxury-duplex-jayanagar', 32000000, NULL, 'Jayanagar 4th Block, Bangalore', 'Bangalore', 'Karnataka', 'duplex', 5, 5, 5200, 8, 10, 3, 'furnished', 'ready', 'An opulent duplex that redefines luxury living in South Bangalore. Spanning two levels with double-height living areas, this masterpiece features a private pool deck, home theatre, and a chef-grade kitchen. The last word in urban luxury.', '["Private Pool Deck","Home Theatre","Smart Home","Gym","Wine Room","Staff Quarters","4-Car Parking","Terrace Garden"]', 1, 1, 'active'),

('Plot in Kanakapura Road', 'plot-kanakapura-road', 4500000, 'Negotiable', 'Kanakapura Road', 'Bangalore', 'Karnataka', 'plot', NULL, NULL, NULL, NULL, NULL, NULL, 'unfurnished', 'ready', 'Prime residential plot in the rapidly developing Kanakapura Road corridor. Excellent connectivity via the upcoming metro line. Ideal for building your dream home or as a high-appreciation investment. BBMP approved layout with all infrastructure.', '["BBMP Approved","Gated Layout","24/7 Security","Underground Drainage","Electricity","Water Connection","Wide Roads"]', 0, 0, 'active');

-- Add sample blog posts
INSERT INTO `blogs` (`title`, `slug`, `excerpt`, `content`, `category`, `status`, `featured`, `author_id`) VALUES
('Top 10 Localities to Invest in Bangalore 2025', 'top-10-localities-invest-bangalore-2025', 'Discover the hottest micro-markets in Bangalore that promise exceptional appreciation and rental yields in 2025 and beyond.', '<p>Bangalore continues to be one of India\'s most dynamic real estate markets. Here are the top localities that offer the best investment potential...</p><h2>1. Whitefield</h2><p>Home to major IT parks and MNCs, Whitefield remains a powerhouse for residential investment...</p>', 'Investment Guide', 'published', 1, 1),
('Complete Guide to Home Loan Process in India', 'complete-guide-home-loan-process-india', 'Everything you need to know about getting a home loan in India — eligibility, documents, interest rates, and pro tips to get the best deal.', '<p>Buying your dream home often requires financial assistance. This comprehensive guide walks you through everything about home loans...</p>', 'Home Buying Guide', 'published', 1, 1),
('RERA Act: Protecting Home Buyers Rights', 'rera-act-protecting-home-buyers-rights', 'Understanding how the Real Estate Regulatory Authority Act safeguards your investment and what rights you have as a home buyer.', '<p>The Real Estate (Regulation and Development) Act, 2016, commonly known as RERA, is one of the most significant reforms...</p>', 'Legal Guide', 'published', 0, 1);

COMMIT;
