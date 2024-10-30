-- Always set 'removed' field to false on insertion.
CREATE OR REPLACE FUNCTION set_removed_to_false() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    new.removed = FALSE;

    RETURN new;
END;
$$;

CREATE TRIGGER bi_user_set_removed_to_false
    BEFORE INSERT
    ON "user"
    FOR EACH ROW
EXECUTE PROCEDURE set_removed_to_false();

-- Only patrons with no active loans can be removed.
CREATE OR REPLACE FUNCTION check_loans_before_removal() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    IF new.removed IS DISTINCT FROM old.removed THEN
        IF old.id IN (SELECT patron FROM loan WHERE returned IS NULL) THEN
            RAISE EXCEPTION 'Only patrons with no active loans can be removed.';
        END IF;
    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bu_user_check_loans_before_removal
    BEFORE UPDATE
    ON "user"
    FOR EACH ROW
EXECUTE PROCEDURE check_loans_before_removal();

-- Deny modification of fields 'email' and 'type'.
CREATE OR REPLACE FUNCTION user_deny_unmodifiable_fields_update() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    IF new.email != old.email THEN
        RAISE EXCEPTION 'Cannot modify email field.';
    END IF;

    IF new.type != old.type THEN
        RAISE EXCEPTION 'Cannot modify type field.';
    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bu_user_deny_unmodifiable_fields_update
    BEFORE UPDATE
    ON "user"
    FOR EACH ROW
EXECUTE PROCEDURE user_deny_unmodifiable_fields_update();


-- Deny deletion of records.
CREATE TRIGGER bd_user_deny_deletion
    BEFORE DELETE
    ON "user"
    FOR EACH ROW
EXECUTE PROCEDURE deny_deletion();