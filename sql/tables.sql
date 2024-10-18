CREATE TABLE "user"
(
    id         UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    email      VARCHAR(100) NOT NULL UNIQUE CHECK (email ~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'),
    password   VARCHAR(100) NOT NULL CHECK (LENGTH(password) > 5),
    first_name VARCHAR(100) NOT NULL CHECK (first_name ~* '^.+$'),
    last_name  VARCHAR(100) NOT NULL CHECK (last_name ~* '^.+$')
);

CREATE TABLE librarian
(
    "user" UUID PRIMARY KEY REFERENCES "user" (id)
);

CREATE TABLE patron_category
(
    "name"     VARCHAR(50) PRIMARY KEY CHECK ("name" ~* '^.+$'),
    loan_limit SMALLINT NOT NULL
);

CREATE TABLE patron
(
    "user"   UUID PRIMARY KEY REFERENCES "user" (id),
    tax_code VARCHAR(100) NOT NULL CHECK (tax_code ~* '^.+$'),
    n_delays SMALLINT     NOT NULL DEFAULT 0,
    category VARCHAR(50)  NOT NULL REFERENCES patron_category ("name")
);

CREATE TABLE loan
(
    start    TIMESTAMP NOT NULL DEFAULT NOW(),
    reader   UUID      NOT NULL REFERENCES patron ("user"),
    copy     SERIAL    NOT NULL REFERENCES book_copy (id),
    due      TIMESTAMP NOT NULL,
    returned TIMESTAMP
);

CREATE TABLE publisher
(
    "name" VARCHAR(100) PRIMARY KEY CHECK ("name" ~* '^.+$')
);

CREATE TABLE branch
(
    id      SERIAL PRIMARY KEY,
    address VARCHAR(200) NOT NULL CHECK (address ~* '^.+$'),
    city    VARCHAR(100) NOT NULL CHECK (city ~* '^.+$'),
    UNIQUE (address, city)
);

CREATE TABLE author
(
    id         SERIAL PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL CHECK (first_name ~* '^.+$'),
    last_name  VARCHAR(100) NOT NULL CHECK (last_name ~* '^.+$'),
    bio        TEXT         NOT NULL CHECK (bio ~* '^.+$'),
    birth_date DATE         NOT NULL,
    death_date DATE
);

CREATE TABLE book
(
    isbn      INT PRIMARY KEY,
    title     VARCHAR(500) NOT NULL CHECK (title ~* '^.+$'),
    blurb     TEXT         NOT NULL CHECK (blurb ~* '^.+$'),
    publisher VARCHAR(100) NOT NULL REFERENCES publisher ("name")
);

CREATE TABLE credits
(
    author SERIAL REFERENCES author (id),
    book   INT REFERENCES book (isbn),
    PRIMARY KEY (author, book)
);

CREATE TABLE book_copy
(
    id     SERIAL PRIMARY KEY,
    branch SERIAL NOT NULL REFERENCES branch (id),
    book   INT    NOT NULL REFERENCES book (isbn)
);






