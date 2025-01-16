create table loan
(
    start    date default CURRENT_DATE                         not null,
    patron   uuid                                              not null
        constraint patron_reference
            references patron,
    copy     integer                                           not null
        references book_copy,
    due      date default (CURRENT_DATE + '30 days'::interval) not null,
    returned date,
    id       serial
        primary key
);