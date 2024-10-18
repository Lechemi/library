CREATE TABLE branch
(
    id      SERIAL PRIMARY KEY,
    address VARCHAR(200) NOT NULL CHECK (address ~* '^.+$'),
    city    VARCHAR(100) NOT NULL CHECK (city ~* '^.+$'),
    UNIQUE (address, city)
);