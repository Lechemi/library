-- Check that the user referenced is a patron.
CREATE OR REPLACE FUNCTION check_user_type_patron() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    _u_type USER_TYPE;
BEGIN
    SELECT type FROM "user" WHERE new."user" = "user".id INTO _u_type;

    IF _u_type <> 'patron' THEN
        RAISE EXCEPTION 'Referenced user is not a patron.';
    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bi_patron_check_user_type
    BEFORE INSERT
    ON patron
    FOR EACH ROW
EXECUTE PROCEDURE check_user_type_patron();

-- Check that the user referenced is not removed.
CREATE OR REPLACE FUNCTION check_user_is_not_removed() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    _user_is_removed BOOLEAN;
BEGIN
    SELECT removed FROM "user" WHERE new."user" = "user".id INTO _user_is_removed;

    IF _user_is_removed THEN
        RAISE EXCEPTION 'Referenced user no longer exists.';
    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bi_patron_check_user_is_not_removed
    BEFORE INSERT
    ON patron
    FOR EACH ROW
EXECUTE PROCEDURE check_user_is_not_removed();

-- Set field 'n_delays' to 0 on insertion.
CREATE OR REPLACE FUNCTION set_default_patron_values() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    new.n_delays := 0;

    RETURN new;
END;
$$;

CREATE TRIGGER bi_patron_set_default_values
    BEFORE INSERT
    ON patron
    FOR EACH ROW
EXECUTE PROCEDURE set_default_patron_values();

/*
    A patron's category can be changed only if they are borrowing no
    more than the new category's loan limit.
 */
CREATE OR REPLACE FUNCTION enforce_category_update_policy() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    _borrowed  SMALLINT;
    _new_limit SMALLINT;
BEGIN

    IF new.category != old.category THEN

        SELECT COUNT(*)
        FROM loan
        WHERE returned IS NULL
          AND patron = new."user"
        INTO _borrowed;

        SELECT loan_limit
        FROM patron_category
        WHERE name = new.category
        INTO _new_limit;

        IF _borrowed > _new_limit THEN
            RAISE EXCEPTION 'A patron''s category can be changed only if they are borrowing no more than the new category''s loan limit.';
        END IF;

    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bu_patron_enforce_category_update_policy
    BEFORE UPDATE
    ON patron
    FOR EACH ROW
EXECUTE PROCEDURE enforce_category_update_policy();

-- Deny deletion of records
CREATE TRIGGER bd_patron_deny_deletion
    BEFORE DELETE
    ON patron
    FOR EACH ROW
EXECUTE PROCEDURE deny_deletion();