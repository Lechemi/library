create table loan
(
    start    timestamp default now()                         not null,
    patron   uuid                                            not null
        constraint patron_reference
            references patron,
    copy     integer                                         not null
        references book_copy,
    due      timestamp default (now() + '30 days'::interval) not null,
    returned timestamp,
    id       serial
        primary key
);