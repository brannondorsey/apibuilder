--
-- Tabelstructuur voor tabel `users_data`
--

CREATE TABLE IF NOT EXISTS `users_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` text NOT NULL,
  `last_name` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `bio` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `first_name` (`first_name`),
  FULLTEXT KEY `last_name` (`last_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Gegevens worden uitgevoerd voor tabel `users_data`
--

INSERT INTO `users_data` (`id`, `first_name`, `last_name`, `email`, `phone_number`, `city`, `state`, `bio`) VALUES
(1, 'Hank', 'Dollar', 'hdollar@hotmail.com', '0615077357', 'Richmond', 'VA', 'Nerd!\n'),
(2, 'Salvester', 'Rinehart', 'salrinehard@gmail.com', '8042557684', 'Richmond', 'VA', 'Total badass.');
