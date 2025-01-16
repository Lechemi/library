-- Set 'removed' field to FALSE by default.
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

-- A currently loaned copy cannot be updated in any way.
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

-- Deny modification if removed (except for 'removed' field)
CREATE OR REPLACE FUNCTION book_copy_deny_update_if_removed() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
BEGIN
    IF old.removed IS TRUE THEN

        if old.branch is distinct from new.branch or
           old.book is distinct from new.book then
            raise exception 'Cannot modify a removed copy.';
        end if;

    END IF;

    RETURN new;
END;
$$;

CREATE TRIGGER bu_book_copy_deny_update_if_removed
    BEFORE UPDATE
    ON book_copy
    FOR EACH ROW
EXECUTE PROCEDURE book_copy_deny_update_if_removed();