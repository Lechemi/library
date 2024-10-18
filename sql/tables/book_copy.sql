CREATE TABLE book_copy
(
    id     SERIAL PRIMARY KEY,
    branch SERIAL NOT NULL REFERENCES branch (id),
    book   INT    NOT NULL REFERENCES book (isbn)
);