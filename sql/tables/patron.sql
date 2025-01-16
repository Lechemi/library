create table patron
(
    "user"   uuid               not null
        primary key
        constraint user_id_reference
            references "user",
    tax_code varchar(100)       not null
        constraint tax_code_unique
            unique
        constraint check_alphanumeric
            check ((tax_code)::text ~ '^[A-Z0-9]{16}$'::text),
    n_delays smallint default 0 not null,
    category varchar(50)        not null
        references patron_category
);