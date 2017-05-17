-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 09, 2017 at 05:25 PM
-- Server version: 10.1.21-MariaDB
-- PHP Version: 7.0.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `task2`
--

-- --------------------------------------------------------

--
-- Table structure for table `groubs`
--

CREATE TABLE `groubs` (
  `id` int(11) NOT NULL,
  `title` varchar(14) NOT NULL,
  `descr` text NOT NULL,
  `admin` int(11) NOT NULL,
  `users` text NOT NULL,
  `pay_per_month` smallint(6) NOT NULL,
  `flag_next` int(11) NOT NULL,
  `status` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `groubs`
--

INSERT INTO `groubs` (`id`, `title`, `descr`, `admin`, `users`, `pay_per_month`, `flag_next`, `status`) VALUES
(1, 'groub1', 'some groub', 1, '1,2', 4000, 1, '\r\n');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_name` varchar(11) NOT NULL,
  `password` varchar(32) NOT NULL,
  `email` text NOT NULL,
  `full_name` text NOT NULL,
  `phoneNo` varchar(15) NOT NULL,
  `addr` text NOT NULL,
  `facebook` text NOT NULL,
  `profilepic` text NOT NULL,
  `reputation` float(10,0) NOT NULL,
  `joind_groubs` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_name`, `password`, `email`, `full_name`, `phoneNo`, `addr`, `facebook`, `profilepic`, `reputation`, `joind_groubs`) VALUES
(1, 'amr', '098f6bcd4621d373cade4e832627b4f6', 'amr@gmail.comgd', 'amr usamad', '01023415203d', 'istanha ,bajour ,menofiad', 'fb/amrusamakasemd', '/imgs/uploads/test.jpgd', 4, '3,5,3,2,4,5d'),
(2, 'amre', 'fd196d87b9d4752fa86a3ddf1481412a', 'amr@gmail.com', 'amr usamad', '01023415203', 'banha city', 'someimage.jpg', 'someimage.jpg', 4, '3,5,3,2,4,5d'),
(5, 'amred', 'fd196d87b9d4752fa86a3ddf1481412a', 'kdlshgklshgkldhg', 'lsdkhglkdshglkhdsg', '23232323', 'banha city', 'sdlkghlksdghh', 'someimage.jpg', 0, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `groubs`
--
ALTER TABLE `groubs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_name` (`user_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `groubs`
--
ALTER TABLE `groubs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
