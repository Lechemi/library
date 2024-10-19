CREATE TABLE book_copy
(
    id     SERIAL PRIMARY KEY,
    branch INT      NOT NULL REFERENCES branch (id),
    book   CHAR(13) NOT NULL REFERENCES book (isbn)
);