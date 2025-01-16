-- Check that the user referenced is not removed and is a patron.
CREATE OR REPLACE FUNCTION check_referenced_user() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    _u_type          USER_TYPE;
    _user_is_removed BOOLEAN;
BEGIN
    SELECT type, removed
    INTO _u_type, _user_is_removed
    FROM "user"
    WHERE new."user" = id;

    IF _user_is_removed THEN
        RAISE EXCEPTION 'Referenced user no longer exists.';
    END IF;

    IF _u_type <> 'patron' THEN
        RAISE EXCEPTION 'Referenced user is not a patron.';
    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bi_patron_check_referenced_user
    BEFORE INSERT
    ON patron
    FOR EACH ROW
EXECUTE PROCEDURE check_referenced_user();

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

-- Deny modification if referenced user is removed
CREATE OR REPLACE FUNCTION patron_deny_update_if_removed() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    _user_is_removed BOOLEAN;
BEGIN

    SELECT removed
    INTO _user_is_removed
    FROM "user"
    WHERE new."user" = id;

    IF _user_is_removed THEN

        if old.tax_code is distinct from new.tax_code or
           old.n_delays is distinct from new.n_delays or
           old.category is distinct from new.category then
            raise exception 'Cannot modify a removed patron.';
        end if;

    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bu_patron_deny_update_if_removed
    BEFORE UPDATE
    ON patron
    FOR EACH ROW
EXECUTE PROCEDURE patron_deny_update_if_removed();

-- Deny deletion of records
CREATE TRIGGER bd_patron_deny_deletion
    BEFORE DELETE
    ON patron
    FOR EACH ROW
EXECUTE PROCEDURE deny_deletion();