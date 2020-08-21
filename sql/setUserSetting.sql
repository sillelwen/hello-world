DELIMITER $$
CREATE PROCEDURE `setUserSetting`(IN `p_name` ENUM('deliveryterms','intervals','rows','notificationsEmail','receptionRegion','receptionCity','receptionAddress','receptionAddressee','deliveryRegion','deliveryCity'), IN `p_value` INT, IN `p_userid` BIGINT UNSIGNED)
    NO SQL
IF (SELECT 1=1 FROM usersettings WHERE userid=p_userid AND name=p_name) THEN

UPDATE `usersettings` SET `value`=p_value WHERE `userid`=p_userid AND `name`=p_name;

ELSE

INSERT INTO `usersettings` (`name`, `value`, `userid`) 
VALUES (p_name, p_value, p_userid);

END IF$$
DELIMITER ;