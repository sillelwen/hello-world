DELIMITER $$
CREATE PROCEDURE `updateRegisterRowStatus`(IN `p_rowid` BIGINT UNSIGNED, IN `p_newstatus` BIGINT UNSIGNED)
    NO SQL
BEGIN

SELECT oldstatus.value as oldStatusName, newstatus.value as newStatusName
FROM register
LEFT JOIN orderstatuses as oldstatus
ON oldstatus.id = register.status
LEFT JOIN orderstatuses as newstatus
ON newstatus.id = p_newstatus
WHERE register.id = p_rowid;

UPDATE register
SET register.status = p_newstatus
WHERE register.id = p_rowid;

END$$
DELIMITER ;