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

-- Check if the patron is removed
CREATE OR REPLACE FUNCTION check_patron_removed_status() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    _patron_is_removed BOOLEAN;
BEGIN
    SELECT removed FROM patron WHERE "user" = new.patron INTO _patron_is_removed;

    IF _patron_is_removed THEN
        RAISE EXCEPTION 'Requesting patron has been removed.';
    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bi_loan_check_patron_removed_status
    BEFORE INSERT
    ON loan
    FOR EACH ROW
EXECUTE PROCEDURE check_patron_removed_status();



-- Deny update if the loan is over
CREATE OR REPLACE FUNCTION check_if_loan_is_over() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    IF old.returned IS NOT NULL THEN
        RAISE EXCEPTION 'Cannot modify an ended loan.';
    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bu_loan_check_if_loan_is_over
    BEFORE UPDATE
    ON loan
    FOR EACH ROW
EXECUTE PROCEDURE check_if_loan_is_over();



-- Deny modification of fields start, patron or copy
CREATE OR REPLACE FUNCTION deny_unmodifiable_fields_update() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    IF new.start != old.start THEN
        RAISE EXCEPTION 'Cannot modify start field.';
    END IF;

    IF new.patron != old.patron THEN
        RAISE EXCEPTION 'Cannot modify patron field.';
    END IF;

    IF new.copy != old.copy THEN
        RAISE EXCEPTION 'Cannot modify copy field.';
    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bu_loan_deny_unmodifiable_fields_update
    BEFORE UPDATE
    ON loan
    FOR EACH ROW
EXECUTE PROCEDURE deny_unmodifiable_fields_update();



-- Allow postponement of due only if the loan is not expired
CREATE OR REPLACE FUNCTION enforce_due_policy() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    IF new.due < old.due THEN
        RAISE EXCEPTION 'Due can only be postponed.';
    END IF;

    IF NOW() > old.due THEN
        RAISE EXCEPTION 'Cannot postpone due because the loan has expired.';
    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bu_loan_enforce_due_policy
    BEFORE UPDATE
    ON loan
    FOR EACH ROW
EXECUTE PROCEDURE enforce_due_policy();



-- Cannot return the book in a past or future date.
CREATE OR REPLACE FUNCTION check_return_timestamp() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    _precision INTERVAL;
BEGIN
    _precision := INTERVAL '5 seconds';
    IF new.returned NOT BETWEEN NOW() - _precision AND NOW() + _precision THEN
        RAISE EXCEPTION 'Cannot return the book in a past or future date.';
    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bu_loan_check_return_timestamp
    BEFORE UPDATE
    ON loan
    FOR EACH ROW
EXECUTE PROCEDURE check_return_timestamp();