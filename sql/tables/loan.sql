CREATE TABLE loan
(
    start    TIMESTAMP NOT NULL,
    patron   UUID      NOT NULL
        REFERENCES patron,
    copy     INTEGER   NOT NULL
        REFERENCES book_copy,
    due      TIMESTAMP NOT NULL,
    returned TIMESTAMP,
    PRIMARY KEY (start, patron, copy)
);