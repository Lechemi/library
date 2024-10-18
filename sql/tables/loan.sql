CREATE TABLE loan
(
    start    TIMESTAMP NOT NULL DEFAULT NOW(),
    reader   UUID      NOT NULL REFERENCES patron ("user"),
    copy     SERIAL    NOT NULL REFERENCES book_copy (id),
    due      TIMESTAMP NOT NULL,
    returned TIMESTAMP
);