DROP PROCEDURE BatchCloseDate;

delimiter $$
CREATE PROCEDURE BatchCloseDate(IN `date_to_close` DATE, IN `systemuserid` BIGINT(20) UNSIGNED)  NO SQL
BEGIN
	DECLARE row_id, userid BIGINT(20) UNSIGNED;
	DECLARE oldstatusname VARCHAR(30);
	DECLARE newstatusname VARCHAR(30);
	DECLARE details, detailstext VARCHAR(90);
	DECLARE done INT DEFAULT FALSE;
	DECLARE cur1 CURSOR FOR 
	SELECT `id`, `statusname` FROM tmp_register;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

	CREATE TEMPORARY TABLE tmp_register
	SELECT register.id, register.userid, register.status, orderstatuses.value as statusname
	FROM register
	LEFT JOIN orderstatuses
	ON register.status = orderstatuses.id
	LEFT JOIN
	receptiondates
	ON receptiondates.userid = register.userid AND receptiondates.date = date_to_close
	WHERE register.receptionDate = date_to_close and register.deleted=0 and receptiondates.date IS NULL;

	UPDATE register
	INNER JOIN tmp_register
	ON register.id = tmp_register.id
	SET register.status = 1
	WHERE tmp_register.status = 0;

	SELECT orderstatuses.value INTO newstatusname
	FROM orderstatuses WHERE
	orderstatuses.id = 1;

	OPEN cur1;

    read_loop: LOOP
		FETCH cur1 INTO row_id, oldstatusname;
		IF done THEN
			LEAVE read_loop;
		END IF;
		
		SET detailstext=CONCAT_WS('', oldstatusname, ' &rarr; ', newstatusname);
		SET details = CONCAT_WS('', 's:', CAST(LENGTH(detailstext) AS CHAR), ':"', detailstext, '";');
		CALL logevent('orderdata', 'BatchCloseDate', systemuserid, '127.0.0.1', 'Изменён статус отправления', row_id, details);
	END LOOP;

	CLOSE cur1;
		
	DROP TEMPORARY TABLE tmp_register;
    
    REPLACE INTO receptiondates (`date`, `userid`)
    SELECT date_to_close, users.id
    FROM users;

	INSERT INTO log
	(type, source, userid, userip, message, objectid, creationDate)
	SELECT
	DISTINCT 'info', 'BatchCloseDate', systemuserid, '127.0.0.1', CONCAT('Выполнено закрытие дня: ', DATE_FORMAT(date_to_close, '%Y-%m-%d')), users.id, NOW()
	FROM users;
	
END;
$$
delimiter ;