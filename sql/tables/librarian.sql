CREATE TABLE librarian
(
    "user" UUID PRIMARY KEY REFERENCES "user" (id)
);