CREATE TABLE IF NOT EXISTS `_PREFIX_likedislike` (
  `post_id` bigint(20) NOT NULL DEFAULT '0',
  `like_dislike` enum('0','1','2','') NOT NULL,
  `ip` bigint(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;