-- Check patron delays
CREATE OR REPLACE FUNCTION check_patron_delays() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    _delays library.patron.N_DELAYS%TYPE;
BEGIN
    SELECT n_delays FROM patron WHERE new.patron = patron."user" INTO _delays;
    IF _delays > 5 THEN
        RAISE EXCEPTION 'Patrons with more than 5 delays cannot loan books.';
    ELSE
        RETURN new;
    END IF;
END;
$$;

CREATE TRIGGER bi_loan_check_delays
    BEFORE INSERT
    ON loan
    FOR EACH ROW
EXECUTE PROCEDURE check_patron_delays();


-- Set default values for columns start, due and returned
CREATE OR REPLACE FUNCTION set_default_loan_values() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    new.start := NOW();
    new.due := NOW() + INTERVAL '30 days';
    new.returned := NULL;

    RETURN new;
END;
$$;

CREATE TRIGGER bi_loan_set_default_values
    BEFORE INSERT
    ON loan
    FOR EACH ROW
EXECUTE PROCEDURE set_default_loan_values();


-- Check if a copy is available
CREATE OR REPLACE FUNCTION check_copy_availability() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    PERFORM * FROM loan WHERE copy = new.copy AND returned IS NULL;
    IF FOUND THEN
        RAISE EXCEPTION 'Requested copy is already on loan.';
    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bi_loan_check_copy_availability
    BEFORE INSERT
    ON loan
    FOR EACH ROW
EXECUTE PROCEDURE check_copy_availability();


-- Check that the user requesting the loan is a patron
CREATE OR REPLACE FUNCTION check_user_is_patron() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    PERFORM * FROM patron WHERE new.patron = patron."user";
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Requesting user must be a patron.';
    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bi_loan_check_user_is_patron
    BEFORE INSERT
    ON loan
    FOR EACH ROW
EXECUTE PROCEDURE check_user_is_patron();

-- Check if the patron would exceed the loan limit
CREATE OR REPLACE FUNCTION check_patron_limit() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    _loaned SMALLINT;
    _limit  SMALLINT;
BEGIN
    SELECT COUNT(*)
    FROM loan
    WHERE returned IS NULL
      AND patron = new.patron
    INTO _loaned;

    SELECT pc.loan_limit
    FROM patron p
             INNER JOIN patron_category pc ON pc.name = p.category
    WHERE p."user" = new.patron
    INTO _limit;

    IF _loaned = _limit THEN
        RAISE EXCEPTION 'Requesting patron has reached the loan limit.';
    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bi_loan_check_patron_limit
    BEFORE INSERT
    ON loan
    FOR EACH ROW
EXECUTE PROCEDURE check_patron_limit();

INSERT INTO loan (patron, copy)
VALUES ('2e46520c-3af6-4883-887a-07cfcbd7e2e8', 7);

UPDATE loan
SET returned = NOW()
WHERE patron = '2e46520c-3af6-4883-887a-07cfcbd7e2e8'
  AND copy = 4;