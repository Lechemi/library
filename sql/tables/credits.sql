create table credits
(
    author integer  not null
        references author,
    book   char(13) not null
        references book,
    primary key (author, book)
);