create table author
(
    id         serial
        primary key,
    first_name varchar(100) not null
        constraint author_first_name_check
            check ((first_name)::text ~* '^.+$'::text),
    last_name  varchar(100) not null
        constraint author_last_name_check
            check ((last_name)::text ~* '^.+$'::text),
    bio        text         not null
        constraint author_bio_check
            check (bio ~* '^.+$'::text),
    birth_date date,
    death_date date,
    alive      boolean      not null,
    constraint unique_author_name
        unique (first_name, last_name),
    constraint check_death_date_after_birth_date
        check ((death_date IS NULL) OR (birth_date IS NULL) OR (death_date > birth_date)),
    constraint check_alive_false_if_dead
        check ((death_date IS NULL) OR (alive = false))
);