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

-- Deny modification of field 'type'.
CREATE OR REPLACE FUNCTION user_deny_unmodifiable_fields_update() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
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

-- Deny modification if removed (except for 'removed' field)
CREATE OR REPLACE FUNCTION user_deny_update_if_removed() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    IF old.removed IS TRUE THEN

        if old.email is distinct from new.email or
           old.password is distinct from new.password or
           old.first_name is distinct from new.first_name or
           old.last_name is distinct from new.last_name then
            raise exception 'Cannot modify a removed user';

        end if;

    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bu_user_deny_update_if_removed
    BEFORE UPDATE
    ON "user"
    FOR EACH ROW
EXECUTE PROCEDURE user_deny_update_if_removed();


-- Deny deletion of records.
CREATE TRIGGER bd_user_deny_deletion
    BEFORE DELETE
    ON "user"
    FOR EACH ROW
EXECUTE PROCEDURE deny_deletion();