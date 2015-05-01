CREATE TABLE `package` (
  `slug` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `download_count` bigint(20),
  PRIMARY KEY (`slug`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `arch_version` (
  `slug` varchar(50) NOT NULL,
  `arch` varchar(10) NOT NULL,
  `version` varchar(20) NOT NULL,
  `stable` tinyint(1) NULL,
  `file_path` varchar(255) NOT NULL,
  `size` bigint(20) NOT NULL,
  `md5` varchar(32) NOT NULL,
  `description` text NOT NULL,
  `qinst` tinyint(1) NOT NULL,
  `deppkgs` varchar(255) NULL,
  `maintainer` varchar(60) NOT NULL,
  `maintainer_url` varchar(255) NULL,
  `distributor` varchar(60) NULL,
  `distributor_url` varchar(255) NULL,
  `beta` tinyint(1) NOT NULL,
  `download_count` bigint(20),
  PRIMARY KEY (`slug`, `arch`, `version`),
  UNIQUE KEY `uniq_stable` (`slug`,`arch`,`stable`),
  CONSTRAINT slug FOREIGN KEY (slug) REFERENCES package(slug) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
