--
-- Table structure for table `users_table`
--

CREATE TABLE IF NOT EXISTS `users_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `API_key` char(40) NOT NULL,
  `API_hits` int(11) NOT NULL,
  `API_hit_date` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Gegevens worden uitgevoerd voor tabel `users_table`
--

INSERT INTO `users_table` (`id`, `API_key`, `API_hits`, `API_hit_date`) VALUES
(1035, '4e13b0c28e17087366ac4d67801ae0835bf9e9a1', 1, '2013-11-15T21:26:43+0100');