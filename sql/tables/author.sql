CREATE TABLE author
(
    id         SERIAL PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL CHECK (first_name ~* '^.+$'),
    last_name  VARCHAR(100) NOT NULL CHECK (last_name ~* '^.+$'),
    bio        TEXT         NOT NULL CHECK (bio ~* '^.+$'),
    birth_date DATE         NOT NULL,
    death_date DATE
);