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
