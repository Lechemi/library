CREATE TABLE book
(
    isbn      CHAR(13) PRIMARY KEY CHECK (isbn ~ '^[0-9]{13}$'),
    title     VARCHAR(500) NOT NULL CHECK (title ~* '^.+$'),
    blurb     TEXT         NOT NULL CHECK (blurb ~* '^.+$'),
    publisher VARCHAR(100) NOT NULL REFERENCES publisher ("name")
);