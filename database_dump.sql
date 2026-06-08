-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 08, 2025 at 04:50 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smart_city_data_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrators`
--

CREATE TABLE `administrators` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `lastLogin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `administrators`
--

INSERT INTO `administrators` (`id`, `name`, `role`, `department`, `email`, `phone`, `status`, `lastLogin`) VALUES
(1, 'Asif', 'System Administrator', 'IT Operations', 'asif.smith@smartcity.gov', '+1-555-0201', 'Active', '2025-11-24 10:18:02'),
(2, 'Nigar', 'Database Administrator', 'IT Operations', 'nigar@smartcity.gov', '+1-555-0202', 'Active', '2025-11-24 10:18:02'),
(3, 'Ahnaf', 'Network Administrator', 'Infrastructure', 'ahnaf@smartcity.gov', '+1-555-0203', 'Active', '2025-11-24 10:18:02'),
(4, 'Maria Garcia', 'Security Administrator', 'Cybersecurity', 'maria.garcia@smartcity.gov', '+1-555-0204', 'Active', '2025-11-24 10:18:02'),
(5, 'Tom Brown', 'Application Administrator', 'Software Services', 'tom.brown@smartcity.gov', '+1-555-0205', 'On Leave', '2025-11-24 10:18:02');

-- --------------------------------------------------------

--
-- Table structure for table `authorities`
--

CREATE TABLE `authorities` (
  `id` int(11) NOT NULL,
  `department` varchar(100) NOT NULL,
  `head` varchar(100) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `employees` varchar(10) DEFAULT NULL,
  `budget` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `authorities`
--

INSERT INTO `authorities` (`id`, `department`, `head`, `contact`, `email`, `address`, `employees`, `budget`) VALUES
(1, 'Traffic Management', 'John Smith', '+1-555-0101', 'traffic@smartcity.gov', 'City Hall, Floor 3', '45', '$2.5M'),
(2, 'Public Works', 'Sarah Johnson', '+1-555-0102', 'publicworks@smartcity.gov', 'Municipal Building A', '120', '$8.2M'),
(3, 'Environmental Services', 'Mike Chen', '+1-555-0103', 'environment@smartcity.gov', 'Green Tower, Suite 200', '68', '$4.1M'),
(4, 'Emergency Services', 'Emily Davis', '+1-555-0104', 'emergency@smartcity.gov', 'Emergency Command Center', '200', '$12.5M'),
(5, 'Urban Planning', 'Robert Wilson', '+1-555-0105', 'planning@smartcity.gov', 'City Hall, Floor 5', '32', '$3.8M');

-- --------------------------------------------------------

--
-- Table structure for table `citizens`
--

CREATE TABLE `citizens` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `registeredDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `citizens`
--

INSERT INTO `citizens` (`id`, `name`, `age`, `address`, `phone`, `email`, `registeredDate`) VALUES
(1, 'Alice Johnson', 34, '123 Elm Street', '+1-555-1001', 'alice.j@email.com', '2024-01-15'),
(2, 'Bob Williams', 45, '456 Oak Avenue', '+1-555-1002', 'bob.w@email.com', '2024-02-20'),
(3, 'Carol Martinez', 29, '789 Pine Road', '+1-555-1003', 'carol.m@email.com', '2024-03-10'),
(4, 'Daniel Brown', 52, '321 Maple Drive', '+1-555-1004', 'daniel.b@email.com', '2024-04-05'),
(5, 'Emma Davis', 38, '654 Cedar Lane', '+1-555-1005', 'emma.d@email.com', '2024-05-12'),
(6, 'Frank Wilson', 41, '987 Birch Street', '+1-555-1006', 'frank.w@email.com', '2024-06-18');

-- --------------------------------------------------------

--
-- Table structure for table `emergency`
--

CREATE TABLE `emergency` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `location` varchar(200) NOT NULL,
  `reportedBy` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `priority` varchar(20) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `assignedUnit` varchar(100) DEFAULT NULL,
  `casualties` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emergency`
--

INSERT INTO `emergency` (`id`, `type`, `location`, `reportedBy`, `status`, `priority`, `timestamp`, `assignedUnit`, `casualties`) VALUES
(1, 'Fire', 'Building A, 23rd Street', 'Citizen Alert', 'In Progress', 'High', '2025-11-24 10:18:02', 'Fire Squad 3', '0'),
(2, 'Road Accident', 'Highway 101, Mile 45', 'Traffic Camera', 'Resolved', 'Medium', '2025-11-24 10:18:02', 'Ambulance 7', '2 injured'),
(3, 'Medical Emergency', 'Park Street Residence', '911 Call', 'Dispatched', 'High', '2025-11-24 10:18:02', 'Ambulance 3', '1 critical'),
(4, 'Flood Warning', 'River Bank Area', 'Weather Station', 'Monitoring', 'Medium', '2025-11-24 10:18:02', 'Disaster Team 2', '0'),
(5, 'Power Outage', 'West District', 'Utility Monitor', 'Under Repair', 'Low', '2025-11-24 10:18:02', 'Electric Crew 5', '0');

-- --------------------------------------------------------

--
-- Table structure for table `energy`
--

CREATE TABLE `energy` (
  `id` int(11) NOT NULL,
  `area` varchar(100) NOT NULL,
  `energyConsumed` varchar(50) DEFAULT NULL,
  `waterUsage` varchar(50) DEFAULT NULL,
  `gasUsage` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `cost` varchar(20) DEFAULT NULL,
  `peakHours` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `energy`
--

INSERT INTO `energy` (`id`, `area`, `energyConsumed`, `waterUsage`, `gasUsage`, `date`, `status`, `cost`, `peakHours`) VALUES
(1, 'Residential Zone A', '1500 kWh', '450 L', '120 m³', '2025-11-24', 'Normal', '$180.00', '06:00 PM - 10:00 PM'),
(2, 'Industrial Zone B', '4200 kWh', '1200 L', '350 m³', '2025-11-24', 'High', '$520.00', '08:00 AM - 06:00 PM'),
(3, 'Commercial Zone C', '2800 kWh', '800 L', '200 m³', '2025-11-24', 'Normal', '$340.00', '10:00 AM - 08:00 PM'),
(4, 'Hospital Complex', '3500 kWh', '1500 L', '280 m³', '2025-11-24', 'Critical', '$450.00', '24/7'),
(5, 'Educational Institute', '1800 kWh', '600 L', '150 m³', '2025-11-24', 'Normal', '$220.00', '08:00 AM - 04:00 PM');

-- --------------------------------------------------------

--
-- Table structure for table `parking`
--

CREATE TABLE `parking` (
  `id` int(11) NOT NULL,
  `location` varchar(200) NOT NULL,
  `totalSpots` int(11) NOT NULL,
  `availableSpots` int(11) NOT NULL,
  `occupiedSpots` int(11) NOT NULL,
  `parkingType` varchar(50) DEFAULT NULL,
  `hourlyRate` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking`
--

INSERT INTO `parking` (`id`, `location`, `totalSpots`, `availableSpots`, `occupiedSpots`, `parkingType`, `hourlyRate`, `status`) VALUES
(1, 'City Center Mall Parking', 200, 45, 155, 'Multi-level', '$3.00', 'Active'),
(2, 'Downtown Plaza', 150, 89, 61, 'Surface', '$2.50', 'Active'),
(3, 'Railway Station Parking', 300, 12, 288, 'Multi-level', '$4.00', 'Active'),
(4, 'Hospital Complex', 180, 67, 113, 'Surface', '$2.00', 'Active'),
(5, 'Sports Stadium', 500, 445, 55, 'Open Ground', '$5.00', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `pollution`
--

CREATE TABLE `pollution` (
  `id` int(11) NOT NULL,
  `location` varchar(200) NOT NULL,
  `aqi` int(11) NOT NULL,
  `pm25` varchar(20) DEFAULT NULL,
  `co2Level` varchar(20) DEFAULT NULL,
  `noiseLevel` varchar(20) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `alertLevel` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pollution`
--

INSERT INTO `pollution` (`id`, `location`, `aqi`, `pm25`, `co2Level`, `noiseLevel`, `status`, `timestamp`, `alertLevel`) VALUES
(1, 'Downtown Center', 145, '65 µg/m³', '420 ppm', '75 dB', 'Moderate', '2025-11-24 10:18:02', 'Yellow'),
(2, 'Industrial Area', 198, '95 µg/m³', '580 ppm', '85 dB', 'Unhealthy', '2025-11-24 10:18:02', 'Orange'),
(3, 'Suburban Park', 78, '32 µg/m³', '380 ppm', '45 dB', 'Good', '2025-11-24 10:18:02', 'Green'),
(4, 'Highway Corridor', 165, '78 µg/m³', '495 ppm', '80 dB', 'Unhealthy for Sensitive', '2025-11-24 10:18:02', 'Yellow'),
(5, 'Coastal Area', 55, '18 µg/m³', '360 ppm', '40 dB', 'Excellent', '2025-11-24 10:18:02', 'Green');

-- --------------------------------------------------------

--
-- Table structure for table `traffic`
--

CREATE TABLE `traffic` (
  `id` int(11) NOT NULL,
  `location` varchar(200) NOT NULL,
  `vehicleCount` int(11) NOT NULL,
  `congestionLevel` varchar(50) DEFAULT NULL,
  `accidents` int(11) DEFAULT 0,
  `timestamp` datetime DEFAULT current_timestamp(),
  `weatherCondition` varchar(50) DEFAULT NULL,
  `speedAvg` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `traffic`
--

INSERT INTO `traffic` (`id`, `location`, `vehicleCount`, `congestionLevel`, `accidents`, `timestamp`, `weatherCondition`, `speedAvg`) VALUES
(1, 'Main Street & 5th Avenue', 245, 'High', 2, '2025-11-24 10:18:02', 'Clear', '15 km/h'),
(2, 'Highway 101 Exit 12', 180, 'Medium', 0, '2025-11-24 10:18:02', 'Rainy', '35 km/h'),
(3, 'Central Business District', 320, 'High', 1, '2025-11-24 10:18:02', 'Clear', '12 km/h'),
(4, 'Airport Road Junction', 95, 'Low', 0, '2025-11-24 10:18:02', 'Foggy', '55 km/h'),
(5, 'Industrial Zone Route 7', 150, 'Medium', 1, '2025-11-24 10:18:02', 'Clear', '40 km/h');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@smartcity.gov', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-11-24 04:18:02'),
(2, 'user', 'user@smartcity.gov', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '2025-11-24 04:18:02'),
(5, 'testuser', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '2025-11-24 05:11:47');

-- --------------------------------------------------------

--
-- Table structure for table `waste`
--

CREATE TABLE `waste` (
  `id` int(11) NOT NULL,
  `wasteCategory` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `recyclingRate` varchar(10) DEFAULT NULL,
  `colorCode` varchar(20) DEFAULT NULL,
  `disposalMethod` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `waste`
--

INSERT INTO `waste` (`id`, `wasteCategory`, `description`, `recyclingRate`, `colorCode`, `disposalMethod`) VALUES
(1, 'Organic', 'Food waste, garden waste', '75%', 'Green', 'Composting'),
(2, 'Plastic', 'Bottles, containers, packaging', '60%', 'Blue', 'Recycling Plant'),
(3, 'Electronic', 'Old devices, batteries, cables', '45%', 'Red', 'E-waste Facility'),
(4, 'Paper', 'Newspapers, cardboard, office paper', '80%', 'Yellow', 'Paper Mill'),
(5, 'Metal', 'Cans, scrap metal', '70%', 'Gray', 'Metal Recycling');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrators`
--
ALTER TABLE `administrators`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `authorities`
--
ALTER TABLE `authorities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `citizens`
--
ALTER TABLE `citizens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `emergency`
--
ALTER TABLE `emergency`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `energy`
--
ALTER TABLE `energy`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `parking`
--
ALTER TABLE `parking`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pollution`
--
ALTER TABLE `pollution`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `traffic`
--
ALTER TABLE `traffic`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `waste`
--
ALTER TABLE `waste`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrators`
--
ALTER TABLE `administrators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `authorities`
--
ALTER TABLE `authorities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `citizens`
--
ALTER TABLE `citizens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `emergency`
--
ALTER TABLE `emergency`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `energy`
--
ALTER TABLE `energy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `parking`
--
ALTER TABLE `parking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pollution`
--
ALTER TABLE `pollution`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `traffic`
--
ALTER TABLE `traffic`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `waste`
--
ALTER TABLE `waste`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;