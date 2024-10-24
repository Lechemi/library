-- Check that the user referenced is a patron
CREATE OR REPLACE FUNCTION check_user_type_patron() RETURNS TRIGGER
    LANGUAGE plpgsql
AS
$$
DECLARE
    u_type user_type;
BEGIN
    SELECT type FROM "user" WHERE new."user" = "user".id INTO u_type;
    IF u_type <> 'patron' THEN
        RAISE EXCEPTION 'Referenced user is not a patron.';
    ELSE
        RETURN new;
    END IF;
END;
$$;

CREATE TRIGGER biu_patron_check_user_type
    BEFORE INSERT OR UPDATE
    ON patron
    FOR EACH ROW
EXECUTE PROCEDURE check_user_type_patron();