create table "user"
(
    id         uuid    default gen_random_uuid() not null
        primary key,
    email      varchar(100)                      not null
        unique
        constraint user_email_check
            check ((email)::text ~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'::text),
    password   varchar(100)                      not null
        constraint user_password_check
            check (length((password)::text) > 5),
    first_name varchar(100)                      not null
        constraint user_first_name_check
            check ((first_name)::text ~* '^.+$'::text),
    last_name  varchar(100)                      not null
        constraint user_last_name_check
            check ((last_name)::text ~* '^.+$'::text),
    type       library.user_type                 not null,
    removed    boolean default false,
    constraint librarian_email_check
        check ((type <> 'librarian'::library.user_type) OR ((email)::text ~~ '%@librarian.com'::text))
);