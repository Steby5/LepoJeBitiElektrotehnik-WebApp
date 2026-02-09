-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Gostitelj: localhost:3306
-- Čas nastanka: 18. feb 2024 ob 09.48
-- Različica strežnika: 8.0.36-0ubuntu0.22.04.1
-- Različica PHP: 8.1.2-1ubuntu2.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Zbirka podatkov: `elektrotehnik`
--

-- --------------------------------------------------------

--
-- Struktura tabele `contestants`
--

CREATE TABLE `contestants` (
  `ID` int NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_slovenian_ci;

-- --------------------------------------------------------

--
-- Struktura tabele `question`
--

CREATE TABLE `question` (
  `questionText` varchar(400) COLLATE utf8mb4_slovenian_ci NOT NULL,
  `AText` varchar(100) COLLATE utf8mb4_slovenian_ci NOT NULL,
  `BText` varchar(100) COLLATE utf8mb4_slovenian_ci NOT NULL,
  `CText` varchar(100) COLLATE utf8mb4_slovenian_ci NOT NULL,
  `DText` varchar(100) COLLATE utf8mb4_slovenian_ci NOT NULL,
  `ACount` int NOT NULL DEFAULT '0',
  `BCount` int NOT NULL DEFAULT '0',
  `CCount` int NOT NULL DEFAULT '0',
  `DCount` int NOT NULL DEFAULT '0',
  `ID` int NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_slovenian_ci;

--
-- Odloži podatke za tabelo `question`
--

INSERT INTO `question` (`questionText`, `AText`, `BText`, `CText`, `DText`, `ACount`, `BCount`, `CCount`, `DCount`, `ID`, `time`) VALUES
('Povprečno koliko študentov_k FE se letno odloči za Erasmus izmenjavo v tujini?', '15', '70', '40', '150', 28, 40, 26, 25, 31, '2024-02-16 09:35:08'),
('Kaj popisuje Kirchoffov zakon?', 'Tok v zanki', 'Upornost v zanki', 'Napetost v zanki', 'Magnetni pretok v zanki', 29, 7, 18, 6, 32, '2024-02-16 10:51:27'),
('Kolikšna je tipična izhodna napetost ene sončne celice pri polni osvetlitvi?', '6,2 V', '3,7 V', '1,2 V', '0,6 V', 6, 9, 8, 10, 33, '2024-02-16 10:57:16'),
('Kje se prvič pojavi beseda \"robot\"?', 'V filmu \"Metropolis\"', 'V Keopsovi piramidi', 'V patentni prijavi prvega postroja', 'V češki drami R.U.R.', 3, 19, 4, 36, 34, '2024-02-16 11:07:07'),
('Povprečno koliko študentov_k FE se letno odloči za Erasmus izmenjavo v tujini?', '15', '70', '40', '150', 4, 29, 19, 23, 35, '2024-02-16 14:21:26'),
('Kako se glasi Ohmov zakon?', 'O=m*G', 'U=R*I', 'W=t*F', 'R=O*f*L', 5, 59, 9, 24, 36, '2024-02-16 14:26:51'),
('S čim merimo aktivnost mišic?', 'STD', 'PVC', 'OMG', 'EMG', 4, 2, 4, 21, 37, '2024-02-16 15:22:24'),
('Katero japonsko podjetje je v Kočevju postavilo prvo evropsko tovarno robotov?', 'Fanuc', 'Kawasaki', 'Yaskawa', 'ABB', 1, 5, 22, 5, 38, '2024-02-16 15:32:32'),
('V sodelovanju s katerim podjetjem je Fakulteta za elektrotehniko leta 1981 začela razvoj robota ROKI?', 'SpaceX', 'Iskra Kibernetika', 'Outfit7', 'Apple', 3, 17, 1, 0, 39, '2024-02-16 15:38:55'),
('Povprečno koliko študentov_k FE se letno odloči za Erasmus izmenjavo v tujini?', '15', '70', '40', '150', 7, 24, 51, 26, 40, '2024-02-17 09:24:04'),
('Kateri elektronski element prevaja električni tok samo v eno smer?', 'Dioda', 'Upor', 'Kondenzator', 'Tuljava', 112, 6, 9, 16, 41, '2024-02-17 09:30:26'),
('Katera sila ima v makroskopskem svetu največjo jakost?', 'Gravitacijska sila', 'Elektromagnetna sila', 'Jedrska sila', 'Višja sila', 6, 42, 28, 7, 42, '2024-02-17 09:38:59'),
('Kot med Zemljino vrtilno osjo in premico, ki gre skozi geomagnetna pola Zemlje, znaša približno:', '45 stopinj', '11 stopinj', '23 stopinj', '14 stopinj', 0, 1, 11, 0, 43, '2024-02-17 10:43:21');

--
-- Indeksi zavrženih tabel
--

--
-- Indeksi tabele `contestants`
--
ALTER TABLE `contestants`
  ADD PRIMARY KEY (`ID`);

--
-- Indeksi tabele `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT zavrženih tabel
--

--
-- AUTO_INCREMENT tabele `contestants`
--
ALTER TABLE `contestants`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=828;

--
-- AUTO_INCREMENT tabele `question`
--
ALTER TABLE `question`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;

--
-- Struktura tabele `dozivetja`
--

CREATE TABLE `dozivetja` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `max_spots` int NOT NULL,
  `barva` varchar(20) NOT NULL DEFAULT '#667eea',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_slovenian_ci;

--
-- Struktura tabele `dozivetja_prijave`
--

CREATE TABLE `dozivetja_prijave` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dozivetje_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `izbran` tinyint(1) NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `dozivetje_id` (`dozivetje_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_slovenian_ci;

