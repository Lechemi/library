CREATE TABLE loan
(
    start    TIMESTAMP DEFAULT NOW(),
    patron   UUID
        CONSTRAINT patron_reference
            REFERENCES patron,
    copy     INTEGER
        REFERENCES book_copy,
    due      TIMESTAMP DEFAULT (NOW() + '30 days'::INTERVAL) NOT NULL,
    returned TIMESTAMP,
    PRIMARY KEY (start, patron, copy)
);