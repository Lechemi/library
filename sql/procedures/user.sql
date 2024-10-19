create procedure create_patron(
    _email "user".email%type,
    _password "user".password%type,
    _first_name "user".first_name%type,
    _last_name "user".last_name%type,
    _tax_code patron.tax_code%type,
    _category patron.category%type
)
language plpgsql
as
$$
declare
    _id uuid;
begin

    insert into "user" (email, password, first_name, last_name, type)
    values (_email, _password, _first_name, _last_name, 'patron')
    returning id into _id;

    insert into patron ("user", tax_code, category)
    values (_id, _tax_code, _category);

end;
$$;