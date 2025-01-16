create table publisher
(
    name varchar(100) not null
        primary key
        constraint publisher_name_check
            check ((name)::text ~* '^.+$'::text)
);