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
(1035, 'Thomas', 'Robinson', 'thomasrobinson@gmail.com', '8042123478', 'Richmond', 'VA', 'I am a teacher in the Richmond City Public School System'),
(850, 'George', 'Gregory', 'gregg@gmail.com', '8043703986', 'Richmond', 'VA', 'I am creative coder from Richmond');
