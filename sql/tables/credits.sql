CREATE TABLE credits
(
    author INT REFERENCES author (id),
    book   CHAR(13) REFERENCES book (isbn),
    PRIMARY KEY (author, book)
);