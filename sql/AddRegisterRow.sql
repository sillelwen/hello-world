DELIMITER $$
CREATE PROCEDURE `AddRegisterRow`(IN `p_userid` BIGINT(20) UNSIGNED, IN `p_waybill` VARCHAR(30), IN `p_receptionDate` DATE, IN `p_addressee` VARCHAR(30), IN `p_address` VARCHAR(150), IN `p_contactFIO` VARCHAR(50), IN `p_contactPhone1` VARCHAR(30), IN `p_contactPhone2` VARCHAR(30), IN `p_places` INT(11), IN `p_weight` DECIMAL(6,1), IN `p_dimension1` SMALLINT(6), IN `p_dimension2` SMALLINT(6), IN `p_dimension3` SMALLINT(6), IN `p_deliveryDay` INT(10) UNSIGNED, IN `p_deliveryDate` DATE, IN `p_timeSpanFrom` INT(11), IN `p_timeSpanTo` INT(11), IN `p_paymentTypeID` INT(10) UNSIGNED, IN `p_sum` DECIMAL(9,2), IN `p_note` VARCHAR(150), IN `p_receptionRegion` INT(11), IN `p_receptionCity` VARCHAR(30), IN `p_receptionAddress` VARCHAR(150), IN `p_receptionContactFIO` VARCHAR(50), IN `p_deliveryRegion` INT(11), IN `p_deliveryCity` VARCHAR(30), IN `p_status` INT(10) UNSIGNED, IN `p_addedFrom` TINYINT(4))
    NO SQL
BEGIN

INSERT INTO register
(userid, waybill, receptionDate, addressee, address, contactFIO, contactPhone1, contactPhone2, places, weight,
dimension1, dimension2, dimension3, deliveryDay, deliveryDate, timeSpanFrom, timeSpanTo, paymentTypeID, sum, note, receptionRegion, receptionCity, receptionAddress, receptionContactFIO, deliveryRegion, deliveryCity, status, addedFrom, creationDate)
VALUES
(p_userid, p_waybill, p_receptionDate, p_addressee, p_address, p_contactFIO, p_contactPhone1, p_contactPhone2, p_places, p_weight,
p_dimension1, p_dimension2, p_dimension3, p_deliveryDay, p_deliveryDate, p_timeSpanFrom, p_timeSpanTo, p_paymentTypeID, p_sum, p_note, p_receptionRegion, p_receptionCity, p_receptionAddress, p_receptionContactFIO, p_deliveryRegion, p_deliveryCity, p_status, p_addedFrom, NOW());

SELECT LAST_INSERT_ID() as rowid;

END$$
DELIMITER ;