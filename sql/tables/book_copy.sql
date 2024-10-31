CREATE TABLE book_copy
(
    id      SERIAL
        PRIMARY KEY,
    branch  SERIAL
        REFERENCES branch,
    book    CHAR(13) NOT NULL
        REFERENCES book,
    removed BOOLEAN DEFAULT FALSE
);