-- Set 'removed' field to FALSE
CREATE OR REPLACE FUNCTION set_default_book_copy_values() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    new.removed := FALSE;

    RETURN new;
END;
$$;

CREATE TRIGGER bi_book_copy_set_default_values
    BEFORE INSERT
    ON book_copy
    FOR EACH ROW
EXECUTE PROCEDURE set_default_book_copy_values();

-- 'removed' field can only go from FALSE to TRUE.
CREATE OR REPLACE FUNCTION book_copy_enforce_removal_policy() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    IF new.removed IS DISTINCT FROM old.removed AND new.removed IS FALSE THEN
        RAISE EXCEPTION 'Removed book copies cannot be restored.';
    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bu_book_copy_enforce_removal_policy
    BEFORE UPDATE
    ON book_copy
    FOR EACH ROW
EXECUTE PROCEDURE book_copy_enforce_removal_policy();

-- A loaned copy cannot be updated in any way
CREATE OR REPLACE FUNCTION deny_update_on_loan() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    IF old.id IN (SELECT copy FROM loan WHERE returned IS NULL) THEN
        RAISE EXCEPTION 'Cannot update a currently loaned copy.';
    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bu_book_copy_deny_update_on_loan
    BEFORE UPDATE
    ON book_copy
    FOR EACH ROW
EXECUTE PROCEDURE deny_update_on_loan();

-- Deny deletion of records
CREATE TRIGGER bd_book_copy_deny_deletion
    BEFORE DELETE
    ON book_copy
    FOR EACH ROW
EXECUTE PROCEDURE deny_deletion();