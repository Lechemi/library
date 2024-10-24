CREATE TABLE loan
(
    start    TIMESTAMP DEFAULT NOW()                         NOT NULL,
    patron   UUID                                            NOT NULL
        CONSTRAINT patron_reference
            REFERENCES patron,
    copy     SERIAL
        REFERENCES book_copy,
    due      TIMESTAMP DEFAULT (NOW() + '30 days'::INTERVAL) NOT NULL,
    returned TIMESTAMP,
    PRIMARY KEY (start, patron, copy)
);