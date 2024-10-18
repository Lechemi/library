CREATE TABLE book
(
    isbn      INT PRIMARY KEY,
    title     VARCHAR(500) NOT NULL CHECK (title ~* '^.+$'),
    blurb     TEXT         NOT NULL CHECK (blurb ~* '^.+$'),
    publisher VARCHAR(100) NOT NULL REFERENCES publisher ("name")
);