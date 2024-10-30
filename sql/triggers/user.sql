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



-- 'removed' field can only go from FALSE to TRUE.
CREATE OR REPLACE FUNCTION user_enforce_removal_policy() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    IF new.removed IS DISTINCT FROM old.removed AND new.removed IS FALSE THEN
        RAISE EXCEPTION 'Removed users cannot be restored.';
    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bu_user_enforce_removal_policy
    BEFORE UPDATE
    ON "user"
    FOR EACH ROW
EXECUTE PROCEDURE user_enforce_removal_policy();

-- Removing a user of type patron triggers removal of the corresponding 'patron' record
CREATE OR REPLACE FUNCTION remove_corresponding_patron() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    IF new.removed IS DISTINCT FROM old.removed THEN

        IF (SELECT removed FROM patron p WHERE p."user" = old.id) IS FALSE THEN
            UPDATE patron p SET removed = TRUE WHERE p."user" = old.id;
        END IF;

    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER au_user_remove_corresponding_patron
    AFTER UPDATE
    ON "user"
    FOR EACH ROW
EXECUTE PROCEDURE remove_corresponding_patron();


-- Deny deletion of records
CREATE TRIGGER bd_user_deny_deletion
    BEFORE DELETE
    ON "user"
    FOR EACH ROW
EXECUTE PROCEDURE deny_deletion();