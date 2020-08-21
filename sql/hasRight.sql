DELIMITER $$
CREATE FUNCTION `hasRight`(`usr` BIGINT UNSIGNED, `rght` BIGINT UNSIGNED) RETURNS tinyint(1)
    NO SQL
return (select count(*)>0 as hasright
from userrights
where userid = usr
and rightid = rght)$$
DELIMITER ;