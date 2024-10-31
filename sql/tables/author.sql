CREATE TABLE author
(
    id         SERIAL
        PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL
        CHECK ((first_name)::TEXT ~* '^.+$'::TEXT),
    last_name  VARCHAR(100) NOT NULL
        CHECK ((last_name)::TEXT ~* '^.+$'::TEXT),
    bio        TEXT         NOT NULL
        CHECK (bio ~* '^.+$'::TEXT),
    birth_date DATE,
    death_date DATE,
    alive      BOOLEAN      NOT NULL,
    CONSTRAINT check_death_date_after_birth_date
        CHECK ((death_date IS NULL) OR (birth_date IS NULL) OR (death_date > birth_date)),
    CONSTRAINT check_alive_false_if_dead
        CHECK ((death_date IS NULL) OR (alive = FALSE))
);