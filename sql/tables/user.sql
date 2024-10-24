CREATE TABLE "user"
(
    id         UUID    DEFAULT gen_random_uuid() NOT NULL
        PRIMARY KEY,
    email      VARCHAR(100)                      NOT NULL
        UNIQUE
        CONSTRAINT user_email_check
            CHECK ((email)::TEXT ~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'::TEXT),
    password   VARCHAR(100)                      NOT NULL
        CONSTRAINT user_password_check
            CHECK (LENGTH((password)::TEXT) > 5),
    first_name VARCHAR(100)                      NOT NULL
        CONSTRAINT user_first_name_check
            CHECK ((first_name)::TEXT ~* '^.+$'::TEXT),
    last_name  VARCHAR(100)                      NOT NULL
        CONSTRAINT user_last_name_check
            CHECK ((last_name)::TEXT ~* '^.+$'::TEXT),
    type       library.USER_TYPE                 NOT NULL,
    removed    BOOLEAN DEFAULT FALSE
);