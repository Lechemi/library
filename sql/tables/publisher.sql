CREATE TABLE publisher
(
    "name" VARCHAR(100) PRIMARY KEY CHECK ("name" ~* '^.+$')
);