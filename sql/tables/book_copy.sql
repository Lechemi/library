create table book_copy
(
    id      serial
        primary key,
    branch  integer  not null
        references branch,
    book    char(13) not null
        constraint book_reference
            references book,
    removed boolean default false
);