CREATE TABLE "user"
(
    id         UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    email      VARCHAR(100) NOT NULL UNIQUE CHECK (email ~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'),
    password   VARCHAR(100) NOT NULL CHECK (LENGTH(password) > 5),
    first_name VARCHAR(100) NOT NULL CHECK (first_name ~* '^.+$'),
    last_name  VARCHAR(100) NOT NULL CHECK (last_name ~* '^.+$')
);