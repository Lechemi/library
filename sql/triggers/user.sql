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








-- Deny deletion of records
CREATE TRIGGER bd_user_deny_deletion
    BEFORE DELETE
    ON "user"
    FOR EACH ROW
EXECUTE PROCEDURE deny_deletion();