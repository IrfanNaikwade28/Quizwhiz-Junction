SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = '+00:00';


CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(500) NOT NULL,
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `admin` (`admin_id`, `email`, `password`) VALUES
(1, 'admin@gmail.com', 'pass123');


CREATE TABLE IF NOT EXISTS `answer` (
  `qid` text NOT NULL,
  `ansid` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `history` (
  `email` varchar(50) NOT NULL,
  `eid` text NOT NULL,
  `score` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `sahi` int(11) NOT NULL,
  `wrong` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `options` (
  `qid` varchar(50) NOT NULL,
  `option` varchar(5000) NOT NULL,
  `optionid` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `questions` (
  `eid` text NOT NULL,
  `qid` text NOT NULL,
  `qns` text NOT NULL,
  `choice` int(10) NOT NULL,
  `sn` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `quiz` (
  `eid` text NOT NULL,
  `title` varchar(100) NOT NULL,
  `sahi` int(11) NOT NULL,
  `wrong` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `rank` (
  `email` varchar(50) NOT NULL,
  `score` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user` (
  `name` varchar(50) NOT NULL,
  `college` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `quiz` (`eid`, `title`, `sahi`, `wrong`, `total`) VALUES
('js-quiz-001', 'JavaScript Basics', 1, 0, 10);

INSERT INTO `questions` (`eid`, `qid`, `qns`, `choice`, `sn`) VALUES
('js-quiz-001', 'jsq1',  'Which keyword declares a block-scoped variable?', 4, 1),
('js-quiz-001', 'jsq2',  'Which operator checks strict equality (value and type)?', 4, 2),
('js-quiz-001', 'jsq3',  'Which method adds an element to the end of an array?', 4, 3),
('js-quiz-001', 'jsq4',  'How do you convert a JSON string to an object?', 4, 4),
('js-quiz-001', 'jsq5',  'What is the result of typeof null?', 4, 5),
('js-quiz-001', 'jsq6',  'Which method registers a success handler on a Promise?', 4, 6),
('js-quiz-001', 'jsq7',  'Which keyword defines a constant?', 4, 7),
('js-quiz-001', 'jsq8',  'Which built-in object is used to work with dates and times?', 4, 8),
('js-quiz-001', 'jsq9',  'Which statement is used to handle exceptions?', 4, 9),
('js-quiz-001', 'jsq10', 'Which array method creates a new array with the results of calling a provided function on every element?', 4, 10);

INSERT INTO `options` (`qid`, `option`, `optionid`) VALUES
('jsq1','var','jsq1_a'),('jsq1','let','jsq1_b'),('jsq1','const','jsq1_c'),('jsq1','function','jsq1_d'),
('jsq2','==','jsq2_a'),('jsq2','===','jsq2_b'),('jsq2','~=','jsq2_c'),('jsq2','!=','jsq2_d'),
('jsq3','push()','jsq3_a'),('jsq3','pop()','jsq3_b'),('jsq3','shift()','jsq3_c'),('jsq3','unshift()','jsq3_d'),
('jsq4','JSON.stringify()','jsq4_a'),('jsq4','JSON.parse()','jsq4_b'),('jsq4','toJSON()','jsq4_c'),('jsq4','parseJSON()','jsq4_d'),
('jsq5','"null"','jsq5_a'),('jsq5','"object"','jsq5_b'),('jsq5','"undefined"','jsq5_c'),('jsq5','"number"','jsq5_d'),
('jsq6','then()','jsq6_a'),('jsq6','catch()','jsq6_b'),('jsq6','resolve()','jsq6_c'),('jsq6','done()','jsq6_d'),
('jsq7','var','jsq7_a'),('jsq7','let','jsq7_b'),('jsq7','const','jsq7_c'),('jsq7','static','jsq7_d'),
('jsq8','Calendar','jsq8_a'),('jsq8','Date','jsq8_b'),('jsq8','Time','jsq8_c'),('jsq8','Clock','jsq8_d'),
('jsq9','try...catch','jsq9_a'),('jsq9','handle','jsq9_b'),('jsq9','except','jsq9_c'),('jsq9','throwable','jsq9_d'),
('jsq10','map()','jsq10_a'),('jsq10','forEach()','jsq10_b'),('jsq10','filter()','jsq10_c'),('jsq10','reduce()','jsq10_d');

INSERT INTO `answer` (`qid`, `ansid`) VALUES
('jsq1','jsq1_b'),
('jsq2','jsq2_b'),
('jsq3','jsq3_a'),
('jsq4','jsq4_b'),
('jsq5','jsq5_b'),
('jsq6','jsq6_a'),
('jsq7','jsq7_c'),
('jsq8','jsq8_b'),
('jsq9','jsq9_a'),
('jsq10','jsq10_a');


ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

COMMIT;
