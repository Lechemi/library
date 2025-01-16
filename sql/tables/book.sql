create table book
(
    isbn      char(13)     not null
        primary key
        constraint book_isbn_check
            check (isbn ~ '^[0-9]{13}$'::text),
    title     varchar(500) not null
        constraint book_title_check
            check ((title)::text ~* '^.+$'::text),
    blurb     text         not null
        constraint book_blurb_check
            check (blurb ~* '^.+$'::text),
    publisher varchar(100) not null
        references publisher
);