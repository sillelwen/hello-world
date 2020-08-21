DELIMITER $$
CREATE FUNCTION `hasNamedRight`(`usr` BIGINT UNSIGNED, `rghtNm` VARCHAR(30)) RETURNS tinyint(1)
    NO SQL
RETURN (SELECT COUNT(*)>0 as hasright FROM userrights INNER JOIN rights ON rights.id = rightId WHERE userid = usr AND rights.name = rghtNm)$$
DELIMITER ;