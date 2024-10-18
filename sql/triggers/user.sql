-- Check that the user referenced is a patron
create or replace function check_user_type_patron() returns trigger
language plpgsql
as
$$
declare
    u_type user_type;
begin
    select type from "user" where new."user" = "user".id into u_type;
    if u_type <> 'patron' then
        raise info 'The referenced user is not a patron.';
        return null;
    else
        return new;
    end if;
end;
$$;

create trigger biu_patron_check_user_type
    before insert or update on patron
    for each row
    execute procedure check_user_type_patron();
