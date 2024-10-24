-- Always set removed field to false on insertion
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



-- Deny modification of fields id, email or type
CREATE OR REPLACE FUNCTION user_deny_unmodifiable_fields_update() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    IF new.id != old.id THEN
        RAISE EXCEPTION 'Cannot modify id field.';
    END IF;

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




-- Deny deletion of records
CREATE TRIGGER bd_user_deny_deletion
    BEFORE DELETE
    ON "user"
    FOR EACH ROW
EXECUTE PROCEDURE deny_deletion();