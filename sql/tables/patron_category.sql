create table patron_category
(
    name       varchar(50) not null
        primary key
        constraint patron_category_name_check
            check ((name)::text ~* '^.+$'::text),
    loan_limit smallint    not null
);