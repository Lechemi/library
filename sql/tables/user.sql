CREATE TABLE "user"
(
    id         UUID    DEFAULT gen_random_uuid() NOT NULL
        PRIMARY KEY,
    email      VARCHAR(100)                      NOT NULL
        UNIQUE
        CHECK ((email)::TEXT ~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'::TEXT),
    password   VARCHAR(100)                      NOT NULL
        CHECK (LENGTH((password)::TEXT) > 5),
    first_name VARCHAR(100)                      NOT NULL
        CHECK ((first_name)::TEXT ~* '^.+$'::TEXT),
    last_name  VARCHAR(100)                      NOT NULL
        CHECK ((last_name)::TEXT ~* '^.+$'::TEXT),
    type       library.USER_TYPE                 NOT NULL,
    removed    BOOLEAN DEFAULT FALSE
);