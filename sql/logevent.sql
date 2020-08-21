DELIMITER $$
CREATE PROCEDURE `logevent`(IN `p_type` ENUM('audit','info','warning','error','orderdata'), IN `p_source` VARCHAR(30), IN `p_userid` BIGINT UNSIGNED, IN `p_userip` VARCHAR(15), IN `p_message` VARCHAR(140), IN `p_objectid` BIGINT UNSIGNED, IN `p_details` TEXT)
    NO SQL
BEGIN

INSERT INTO log
(type, source, userid, userip, message, objectid, creationDate)
VALUES
(p_type, p_source, p_userid, p_userip, p_message, p_objectid, NOW());

SET @rowid=LAST_INSERT_ID();

IF p_details IS NOT NULL AND p_details != '' THEN
	INSERT INTO logdetails (id, details) VALUES (@rowid, p_details);
END IF;

END$$
DELIMITER ;