create table branch
(
    id      serial
        primary key,
    address varchar(200) not null
        constraint branch_address_check
            check ((address)::text ~* '^.+$'::text),
    city    varchar(100) not null
        constraint branch_city_check
            check ((city)::text ~* '^.+$'::text),
    name    varchar(100) not null
        constraint branch_name_check
            check ((name)::text ~* '^.+$'::text),
    unique (address, city)
);