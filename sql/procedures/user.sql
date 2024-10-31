-- Creates a user of type patron and a corresponding 'patron' record.
CREATE PROCEDURE create_patron(
    _email "user".EMAIL%TYPE,
    _password "user".PASSWORD%TYPE,
    _first_name "user".FIRST_NAME%TYPE,
    _last_name "user".LAST_NAME%TYPE,
    _tax_code patron.TAX_CODE%TYPE,
    _category patron.CATEGORY%TYPE
)
    LANGUAGE plpgsql
AS
$$
DECLARE
    _id UUID;
BEGIN

    INSERT INTO "user" (email, password, first_name, last_name, type)
    VALUES (_email, _password, _first_name, _last_name, 'patron')
    RETURNING id INTO _id;

    INSERT INTO patron ("user", tax_code, category)
    VALUES (_id, _tax_code, _category);

END;
$$;