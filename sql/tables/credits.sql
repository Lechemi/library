CREATE TABLE credits
(
    author SERIAL REFERENCES author (id),
    book   INT REFERENCES book (isbn),
    PRIMARY KEY (author, book)
);