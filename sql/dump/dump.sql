--
-- PostgreSQL database dump
--

-- Dumped from database version 17.0 (Postgres.app)
-- Dumped by pg_dump version 17.2

-- Started on 2025-01-20 09:42:44 CET

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 7 (class 2615 OID 18208)
-- Name: library; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA library;


--
-- TOC entry 2 (class 3079 OID 18586)
-- Name: pgcrypto; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA library;


--
-- TOC entry 3896 (class 0 OID 0)
-- Dependencies: 2
-- Name: EXTENSION pgcrypto; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION pgcrypto IS 'cryptographic functions';


--
-- TOC entry 945 (class 1247 OID 18624)
-- Name: user_type; Type: TYPE; Schema: library; Owner: -
--

CREATE TYPE library.user_type AS ENUM (
    'patron',
    'librarian'
);


--
-- TOC entry 304 (class 1255 OID 27175)
-- Name: book_copy_deny_update_if_removed(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.book_copy_deny_update_if_removed() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF old.removed IS TRUE THEN

        if old.branch is distinct from new.branch or
           old.book is distinct from new.book then
            raise exception 'Cannot modify a removed copy.';
        end if;

    END IF;

    RETURN new;
END;
$$;


--
-- TOC entry 298 (class 1255 OID 18777)
-- Name: check_copy_availability(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.check_copy_availability() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    _copy_is_removed BOOLEAN;
BEGIN
    SELECT removed FROM book_copy WHERE id = new.copy INTO _copy_is_removed;
    IF _copy_is_removed THEN
        RAISE EXCEPTION 'Requested copy has been removed from the catalogue.';
    END IF;

    PERFORM * FROM loan WHERE copy = new.copy AND returned IS NULL;
    IF FOUND THEN
        RAISE EXCEPTION 'Requested copy is already on loan.';
    END IF;

    RETURN new;
END;
$$;


--
-- TOC entry 293 (class 1255 OID 18803)
-- Name: check_if_loan_is_over(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.check_if_loan_is_over() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF old.returned IS NOT NULL THEN
        RAISE EXCEPTION 'Cannot modify an ended loan.';
    END IF;

    RETURN new;
END;
$$;


--
-- TOC entry 301 (class 1255 OID 18904)
-- Name: check_loans_before_removal(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.check_loans_before_removal() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF new.removed IS DISTINCT FROM old.removed THEN
        IF old.id IN (SELECT patron FROM loan WHERE returned IS NULL) THEN
            RAISE EXCEPTION 'Only patrons with no active loans can be removed.';
        END IF;
    END IF;

    RETURN new;
END;
$$;


--
-- TOC entry 300 (class 1255 OID 18769)
-- Name: check_patron_delays(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.check_patron_delays() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    _delays library.patron.N_DELAYS%TYPE;
BEGIN
    SELECT n_delays FROM patron WHERE new.patron = patron."user" INTO _delays;
    IF _delays >= 5 THEN
        RAISE EXCEPTION 'Patrons with 5 or more delays cannot loan books.';
    ELSE
        RETURN new;
    END IF;
END;
$$;


--
-- TOC entry 291 (class 1255 OID 18781)
-- Name: check_patron_limit(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.check_patron_limit() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    _loaned SMALLINT;
    _limit  SMALLINT;
BEGIN
    SELECT COUNT(*)
    FROM loan
    WHERE returned IS NULL
      AND patron = new.patron
    INTO _loaned;

    SELECT pc.loan_limit
    FROM patron p
             INNER JOIN patron_category pc ON pc.name = p.category
    WHERE p."user" = new.patron
    INTO _limit;

    IF _loaned = _limit THEN
        RAISE EXCEPTION 'Requesting patron has reached the loan limit.';
    END IF;

    RETURN new;
END;
$$;


--
-- TOC entry 305 (class 1255 OID 18932)
-- Name: check_referenced_user(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.check_referenced_user() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    _u_type          USER_TYPE;
    _user_is_removed BOOLEAN;
BEGIN
    SELECT type, removed
    INTO _u_type, _user_is_removed
    FROM "user"
    WHERE new."user" = id;

    IF _user_is_removed THEN
        RAISE EXCEPTION 'Referenced user no longer exists.';
    END IF;

    IF _u_type <> 'patron' THEN
        RAISE EXCEPTION 'Referenced user is not a patron.';
    END IF;

    RETURN new;
END;
$$;


--
-- TOC entry 303 (class 1255 OID 27167)
-- Name: check_return_date(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.check_return_date() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF new.returned IS DISTINCT FROM old.returned AND new.returned != CURRENT_DATE THEN
        RAISE EXCEPTION 'Cannot return the copy in a past or future date.';
    END IF;

    RETURN new;
END;
$$;


--
-- TOC entry 278 (class 1255 OID 18659)
-- Name: create_patron(character varying, character varying, character varying, character varying, character varying, character varying); Type: PROCEDURE; Schema: library; Owner: -
--

CREATE PROCEDURE library.create_patron(IN _email character varying, IN _password character varying, IN _first_name character varying, IN _last_name character varying, IN _tax_code character varying, IN _category character varying)
    LANGUAGE plpgsql
    AS $$
declare
    _id uuid;
begin

    insert into "user" (email, password, first_name, last_name, type)
    values (_email, _password, _first_name, _last_name, 'patron')
    returning id into _id;

    insert into patron ("user", tax_code, category)
    values (_id, _tax_code, _category);

end;
$$;


--
-- TOC entry 308 (class 1255 OID 27170)
-- Name: delays(integer); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.delays(my_branch integer) RETURNS TABLE(patron uuid, book character, copy integer, due date)
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN QUERY
        SELECT loan.patron AS patron, bc.book AS book, bc.id AS copy, loan.due as due
        FROM loan
                 INNER JOIN library.book_copy bc ON bc.id = loan.copy
        WHERE returned IS NULL
          AND bc.branch = my_branch
          AND CURRENT_DATE > loan.due;
END;
$$;


--
-- TOC entry 307 (class 1255 OID 27140)
-- Name: deny_already_loaned_book(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.deny_already_loaned_book() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    PERFORM
    FROM loan
             INNER JOIN book_copy ON loan.copy = book_copy.id
    WHERE patron = new.patron
      AND book = (SELECT book FROM book_copy WHERE id = new.copy)
      AND returned IS NULL;

    IF FOUND THEN
        RAISE EXCEPTION 'Patron is already borrowing the requested book.';
    END IF;

    RETURN new;
END;
$$;


--
-- TOC entry 274 (class 1255 OID 18817)
-- Name: deny_deletion(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.deny_deletion() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    RAISE EXCEPTION 'Deletion is not allowed.';
END;
$$;


--
-- TOC entry 294 (class 1255 OID 18805)
-- Name: deny_unmodifiable_fields_update(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.deny_unmodifiable_fields_update() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    if new.start != old.start then
        RAISE EXCEPTION 'Cannot modify start field.';
    END IF;

    if new.patron != old.patron then
        RAISE EXCEPTION 'Cannot modify patron field.';
    END IF;

    if new.copy != old.copy then
        RAISE EXCEPTION 'Cannot modify copy field.';
    END IF;

    RETURN new;
END;
$$;


--
-- TOC entry 280 (class 1255 OID 18894)
-- Name: deny_update_on_loan(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.deny_update_on_loan() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF old.id IN (SELECT copy FROM loan WHERE returned IS NULL) THEN
        RAISE EXCEPTION 'Cannot update a currently loaned copy.';
    END IF;

    RETURN new;
END;
$$;


--
-- TOC entry 296 (class 1255 OID 18839)
-- Name: enforce_category_update_policy(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.enforce_category_update_policy() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    _borrowed  SMALLINT;
    _new_limit SMALLINT;
BEGIN

    IF new.category != old.category THEN

        SELECT COUNT(*)
        FROM loan
        WHERE returned IS NULL
          AND patron = new."user"
        INTO _borrowed;

        SELECT loan_limit
        FROM patron_category
        WHERE name = new.category
        INTO _new_limit;

        IF _borrowed > _new_limit THEN
            RAISE EXCEPTION 'A patron''s category can be changed only if they are borrowing no more than the new category''s loan limit.';
        END IF;

    END IF;

    RETURN new;
END;
$$;


--
-- TOC entry 295 (class 1255 OID 18809)
-- Name: enforce_due_policy(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.enforce_due_policy() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF new.due != old.due THEN
        IF new.due < old.due THEN
            RAISE EXCEPTION 'Due can only be postponed.';
        END IF;

        IF NOW() > old.due THEN
            RAISE EXCEPTION 'Cannot postpone due because the loan has expired.';
        END IF;
    END IF;

    RETURN new;
END;
$$;


--
-- TOC entry 297 (class 1255 OID 18813)
-- Name: increment_patron_delay_counter(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.increment_patron_delay_counter() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF new.returned IS DISTINCT FROM old.returned AND new.returned > old.due THEN
        UPDATE patron
        SET n_delays = n_delays + 1
        WHERE "user" = old.patron;
    END IF;

    RETURN new;
END;
$$;


--
-- TOC entry 309 (class 1255 OID 18943)
-- Name: make_loan(character, uuid, integer[]); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.make_loan(_book character, _patron uuid, _preferred_branches integer[] DEFAULT NULL::integer[]) RETURNS TABLE(_loaned_copy integer, _loan_branch integer)
    LANGUAGE plpgsql
    AS $$
DECLARE
    _copy              BOOK_COPY%ROWTYPE;
    _available_copies  BOOK_COPY%ROWTYPE[];
    _possible_branches branch.ID%TYPE[];
BEGIN

    IF _preferred_branches IS NULL THEN
        _possible_branches := ARRAY(SELECT id FROM branch);
    ELSE
        _possible_branches := _preferred_branches;
    END IF;

    _available_copies := ARRAY(
            SELECT ROW (id, branch, book, removed)
            FROM book_copy
            WHERE book = _book
              AND id NOT IN (SELECT copy
                             FROM loan
                             WHERE returned IS NULL)
              AND removed = FALSE
                         );

    IF ARRAY_LENGTH(_available_copies, 1) IS NULL THEN
        RAISE EXCEPTION 'There are no copies available for book %.', _book;
    END IF;

    FOREACH _copy IN ARRAY _available_copies
        LOOP

            IF _copy.branch = ANY (_possible_branches) THEN
                INSERT INTO loan (patron, copy)
                VALUES (_patron, _copy.id);

                RETURN QUERY SELECT _copy.id, _copy.branch;

                RETURN;
            END IF;

        END LOOP;

    RAISE EXCEPTION 'No copies available in specified branches for book %.',
        _book;
END;
$$;


--
-- TOC entry 306 (class 1255 OID 27173)
-- Name: patron_deny_update_if_removed(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.patron_deny_update_if_removed() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    _user_is_removed BOOLEAN;
BEGIN

    SELECT removed
    INTO _user_is_removed
    FROM "user"
    WHERE new."user" = id;

    IF _user_is_removed THEN

        if old.tax_code is distinct from new.tax_code or
           old.n_delays is distinct from new.n_delays or
           old.category is distinct from new.category then
            raise exception 'Cannot modify a removed patron.';
        end if;

    END IF;

    RETURN new;
END;
$$;


--
-- TOC entry 275 (class 1255 OID 18842)
-- Name: set_default_book_copy_values(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.set_default_book_copy_values() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    new.removed := FALSE;

    RETURN new;
END;
$$;


--
-- TOC entry 276 (class 1255 OID 18774)
-- Name: set_default_loan_values(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.set_default_loan_values() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    new.start := CURRENT_DATE;
    new.due := (CURRENT_DATE + INTERVAL '30 days');
    new.returned := NULL;

    RETURN new;
END;
$$;


--
-- TOC entry 302 (class 1255 OID 18832)
-- Name: set_default_patron_values(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.set_default_patron_values() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    new.n_delays := 0;

    RETURN new;
END;
$$;


--
-- TOC entry 277 (class 1255 OID 18821)
-- Name: set_removed_to_false(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.set_removed_to_false() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    new.removed = FALSE;

    RETURN new;
END;
$$;


--
-- TOC entry 279 (class 1255 OID 18824)
-- Name: user_deny_unmodifiable_fields_update(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.user_deny_unmodifiable_fields_update() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF new.type != old.type THEN
        RAISE EXCEPTION 'Cannot modify type field.';
    END IF;

    RETURN new;
END;
$$;


--
-- TOC entry 299 (class 1255 OID 27171)
-- Name: user_deny_update_if_removed(); Type: FUNCTION; Schema: library; Owner: -
--

CREATE FUNCTION library.user_deny_update_if_removed() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF old.removed IS TRUE THEN

        if old.email is distinct from new.email or
           old.password is distinct from new.password or
           old.first_name is distinct from new.first_name or
           old.last_name is distinct from new.last_name then
            raise exception 'Cannot modify a removed user';

        end if;

    END IF;

    RETURN new;
END;
$$;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 224 (class 1259 OID 18506)
-- Name: book_copy; Type: TABLE; Schema: library; Owner: -
--

CREATE TABLE library.book_copy (
    id integer NOT NULL,
    branch integer NOT NULL,
    book character(13) NOT NULL,
    removed boolean DEFAULT false
);


--
-- TOC entry 220 (class 1259 OID 18434)
-- Name: branch; Type: TABLE; Schema: library; Owner: -
--

CREATE TABLE library.branch (
    id integer NOT NULL,
    address character varying(200) NOT NULL,
    city character varying(100) NOT NULL,
    name character varying(100) NOT NULL,
    CONSTRAINT branch_address_check CHECK (((address)::text ~* '^.+$'::text)),
    CONSTRAINT branch_city_check CHECK (((city)::text ~* '^.+$'::text)),
    CONSTRAINT branch_name_check CHECK (((name)::text ~* '^.+$'::text))
);


--
-- TOC entry 226 (class 1259 OID 18524)
-- Name: loan; Type: TABLE; Schema: library; Owner: -
--

CREATE TABLE library.loan (
    start date DEFAULT CURRENT_DATE NOT NULL,
    patron uuid NOT NULL,
    copy integer NOT NULL,
    due date DEFAULT (CURRENT_DATE + '30 days'::interval) NOT NULL,
    returned date,
    id integer NOT NULL
);


--
-- TOC entry 235 (class 1259 OID 27160)
-- Name: active_loans; Type: VIEW; Schema: library; Owner: -
--

CREATE VIEW library.active_loans AS
 WITH currently_loaned_copies AS (
         SELECT book_copy.id,
            book_copy.branch
           FROM library.book_copy
          WHERE (book_copy.id IN ( SELECT loan.copy
                   FROM library.loan
                  WHERE (loan.returned IS NULL)))
        )
 SELECT b.id AS branch,
    count(c.id) AS active_loans
   FROM (library.branch b
     LEFT JOIN currently_loaned_copies c ON ((b.id = c.branch)))
  GROUP BY b.id;


--
-- TOC entry 231 (class 1259 OID 18686)
-- Name: author; Type: TABLE; Schema: library; Owner: -
--

CREATE TABLE library.author (
    id integer NOT NULL,
    first_name character varying(100) NOT NULL,
    last_name character varying(100) NOT NULL,
    bio text NOT NULL,
    birth_date date,
    death_date date,
    alive boolean NOT NULL,
    CONSTRAINT author_bio_check CHECK ((bio ~* '^.+$'::text)),
    CONSTRAINT author_first_name_check CHECK (((first_name)::text ~* '^.+$'::text)),
    CONSTRAINT author_last_name_check CHECK (((last_name)::text ~* '^.+$'::text)),
    CONSTRAINT check_alive_false_if_dead CHECK (((death_date IS NULL) OR (alive = false))),
    CONSTRAINT check_death_date_after_birth_date CHECK (((death_date IS NULL) OR (birth_date IS NULL) OR (death_date > birth_date)))
);


--
-- TOC entry 230 (class 1259 OID 18685)
-- Name: author_id_seq; Type: SEQUENCE; Schema: library; Owner: -
--

CREATE SEQUENCE library.author_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3897 (class 0 OID 0)
-- Dependencies: 230
-- Name: author_id_seq; Type: SEQUENCE OWNED BY; Schema: library; Owner: -
--

ALTER SEQUENCE library.author_id_seq OWNED BY library.author.id;


--
-- TOC entry 232 (class 1259 OID 18700)
-- Name: book; Type: TABLE; Schema: library; Owner: -
--

CREATE TABLE library.book (
    isbn character(13) NOT NULL,
    title character varying(500) NOT NULL,
    blurb text NOT NULL,
    publisher character varying(100) NOT NULL,
    CONSTRAINT book_blurb_check CHECK ((blurb ~* '^.+$'::text)),
    CONSTRAINT book_isbn_check CHECK ((isbn ~ '^[0-9]{13}$'::text)),
    CONSTRAINT book_title_check CHECK (((title)::text ~* '^.+$'::text))
);


--
-- TOC entry 223 (class 1259 OID 18505)
-- Name: book_copy_branch_seq; Type: SEQUENCE; Schema: library; Owner: -
--

CREATE SEQUENCE library.book_copy_branch_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3898 (class 0 OID 0)
-- Dependencies: 223
-- Name: book_copy_branch_seq; Type: SEQUENCE OWNED BY; Schema: library; Owner: -
--

ALTER SEQUENCE library.book_copy_branch_seq OWNED BY library.book_copy.branch;


--
-- TOC entry 222 (class 1259 OID 18504)
-- Name: book_copy_id_seq; Type: SEQUENCE; Schema: library; Owner: -
--

CREATE SEQUENCE library.book_copy_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3899 (class 0 OID 0)
-- Dependencies: 222
-- Name: book_copy_id_seq; Type: SEQUENCE OWNED BY; Schema: library; Owner: -
--

ALTER SEQUENCE library.book_copy_id_seq OWNED BY library.book_copy.id;


--
-- TOC entry 219 (class 1259 OID 18433)
-- Name: branch_id_seq; Type: SEQUENCE; Schema: library; Owner: -
--

CREATE SEQUENCE library.branch_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3900 (class 0 OID 0)
-- Dependencies: 219
-- Name: branch_id_seq; Type: SEQUENCE OWNED BY; Schema: library; Owner: -
--

ALTER SEQUENCE library.branch_id_seq OWNED BY library.branch.id;


--
-- TOC entry 233 (class 1259 OID 18733)
-- Name: credits; Type: TABLE; Schema: library; Owner: -
--

CREATE TABLE library.credits (
    author integer NOT NULL,
    book character(13) NOT NULL
);


--
-- TOC entry 225 (class 1259 OID 18523)
-- Name: loan_copy_seq; Type: SEQUENCE; Schema: library; Owner: -
--

CREATE SEQUENCE library.loan_copy_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3901 (class 0 OID 0)
-- Dependencies: 225
-- Name: loan_copy_seq; Type: SEQUENCE OWNED BY; Schema: library; Owner: -
--

ALTER SEQUENCE library.loan_copy_seq OWNED BY library.loan.copy;


--
-- TOC entry 234 (class 1259 OID 18966)
-- Name: loan_id_seq; Type: SEQUENCE; Schema: library; Owner: -
--

CREATE SEQUENCE library.loan_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3902 (class 0 OID 0)
-- Dependencies: 234
-- Name: loan_id_seq; Type: SEQUENCE OWNED BY; Schema: library; Owner: -
--

ALTER SEQUENCE library.loan_id_seq OWNED BY library.loan.id;


--
-- TOC entry 237 (class 1259 OID 27185)
-- Name: managed_books; Type: VIEW; Schema: library; Owner: -
--

CREATE VIEW library.managed_books AS
 SELECT branch.id AS branch,
    count(DISTINCT bc.book) AS n_books
   FROM (library.branch
     LEFT JOIN ( SELECT book_copy.id,
            book_copy.branch,
            book_copy.book,
            book_copy.removed
           FROM library.book_copy
          WHERE (book_copy.removed IS FALSE)) bc ON ((branch.id = bc.branch)))
  GROUP BY branch.id;


--
-- TOC entry 236 (class 1259 OID 27181)
-- Name: managed_copies; Type: VIEW; Schema: library; Owner: -
--

CREATE VIEW library.managed_copies AS
 SELECT branch.id AS branch,
    count(bc.id) AS n_copies
   FROM (library.branch
     LEFT JOIN ( SELECT book_copy.id,
            book_copy.branch,
            book_copy.book,
            book_copy.removed
           FROM library.book_copy
          WHERE (book_copy.removed IS FALSE)) bc ON ((branch.id = bc.branch)))
  GROUP BY branch.id;


--
-- TOC entry 228 (class 1259 OID 18557)
-- Name: patron; Type: TABLE; Schema: library; Owner: -
--

CREATE TABLE library.patron (
    "user" uuid NOT NULL,
    tax_code character varying(100) NOT NULL,
    n_delays smallint DEFAULT 0 NOT NULL,
    category character varying(50) NOT NULL,
    CONSTRAINT check_alphanumeric CHECK (((tax_code)::text ~ '^[A-Z0-9]{16}$'::text))
);


--
-- TOC entry 227 (class 1259 OID 18551)
-- Name: patron_category; Type: TABLE; Schema: library; Owner: -
--

CREATE TABLE library.patron_category (
    name character varying(50) NOT NULL,
    loan_limit smallint NOT NULL,
    CONSTRAINT patron_category_name_check CHECK (((name)::text ~* '^.+$'::text))
);


--
-- TOC entry 221 (class 1259 OID 18456)
-- Name: publisher; Type: TABLE; Schema: library; Owner: -
--

CREATE TABLE library.publisher (
    name character varying(100) NOT NULL,
    CONSTRAINT publisher_name_check CHECK (((name)::text ~* '^.+$'::text))
);


--
-- TOC entry 229 (class 1259 OID 18641)
-- Name: user; Type: TABLE; Schema: library; Owner: -
--

CREATE TABLE library."user" (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    email character varying(100) NOT NULL,
    password character varying(100) NOT NULL,
    first_name character varying(100) NOT NULL,
    last_name character varying(100) NOT NULL,
    type library.user_type NOT NULL,
    removed boolean DEFAULT false,
    CONSTRAINT librarian_email_check CHECK (((type <> 'librarian'::library.user_type) OR ((email)::text ~~ '%@librarian.com'::text))),
    CONSTRAINT user_email_check CHECK (((email)::text ~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'::text)),
    CONSTRAINT user_first_name_check CHECK (((first_name)::text ~* '^.+$'::text)),
    CONSTRAINT user_last_name_check CHECK (((last_name)::text ~* '^.+$'::text)),
    CONSTRAINT user_password_check CHECK ((length((password)::text) > 5))
);


--
-- TOC entry 3646 (class 2604 OID 18689)
-- Name: author id; Type: DEFAULT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.author ALTER COLUMN id SET DEFAULT nextval('library.author_id_seq'::regclass);


--
-- TOC entry 3638 (class 2604 OID 18509)
-- Name: book_copy id; Type: DEFAULT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.book_copy ALTER COLUMN id SET DEFAULT nextval('library.book_copy_id_seq'::regclass);


--
-- TOC entry 3637 (class 2604 OID 18437)
-- Name: branch id; Type: DEFAULT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.branch ALTER COLUMN id SET DEFAULT nextval('library.branch_id_seq'::regclass);


--
-- TOC entry 3642 (class 2604 OID 18967)
-- Name: loan id; Type: DEFAULT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.loan ALTER COLUMN id SET DEFAULT nextval('library.loan_id_seq'::regclass);


--
-- TOC entry 3887 (class 0 OID 18686)
-- Dependencies: 231
-- Data for Name: author; Type: TABLE DATA; Schema: library; Owner: -
--

COPY library.author (id, first_name, last_name, bio, birth_date, death_date, alive) FROM stdin;
1	Elena	Ferrante	Scrittrice italiana, conosciuta per la sua serie L'amica geniale, che ha avuto un grande successo internazionale. La sua identità rimane misteriosa.	\N	\N	t
2	Haruki	Murakami	Scrittore giapponese noto per il suo stile surreale e visionario. Tra i suoi libri più famosi ci sono Norwegian Wood, Kafka sulla spiaggia, e 1Q84	1949-01-12	\N	t
36	Giovanni	Verga	È stato uno scrittore, drammaturgo e politico italiano, considerato il maggior esponente della corrente letteraria del Verismo.	1840-09-02	1922-01-27	f
37	Luigi	Pirandello	Pirandello è celebre per i suoi romanzi, racconti e soprattutto per le opere teatrali che hanno rivoluzionato il teatro moderno. Il suo stile è caratterizzato da un'acuta analisi psicologica e filosofica, con particolare attenzione ai temi dell'identità, della maschera sociale e della relatività della verità.	1867-06-28	1936-12-10	f
6	Neil	Gaiman	Nato il 10 novembre 1960 in Inghilterra, Neil Gaiman è uno scrittore di narrativa fantastica, fumetti e sceneggiature. È noto per la sua capacità di intrecciare miti e realtà in storie accattivanti e immaginifiche. Tra le sue opere più famose vi sono American Gods, Coraline, e la serie a fumetti The Sandman. Gaiman ha ricevuto numerosi premi letterari e continua a scrivere romanzi, adattamenti televisivi e sceneggiature per film.	1960-11-10	\N	t
8	Terry	Pratchett	Sir Terence David John Pratchett, meglio conosciuto come Terry Pratchett, è nato il 28 aprile 1948 in Inghilterra e si è affermato come uno degli scrittori di fantasy più influenti del XX secolo. Celebre per la sua serie Discworld, composta da oltre 40 libri, ha conquistato i lettori con il suo stile umoristico e la sua satira sociale. Nel 2009, a Pratchett è stato conferito il titolo di Cavaliere dalla Regina Elisabetta II per i suoi contributi alla letteratura. È scomparso il 12 marzo 2015.	1948-04-28	2015-03-12	f
3	Italo	Calvino	Scrittore e saggista italiano, autore di opere celebri come Il barone rampante, Le città invisibili, e Se una notte d'inverno un viaggiatore. È uno degli autori italiani più influenti del XX secolo.	1923-10-15	1985-09-19	f
25	Stephen	King	Stephen King, often referred to as the "King of Horror," is a prolific and critically acclaimed American author. With a career spanning decades, he has written over 60 novels and hundreds of short stories, many of which have become bestsellers and cultural touchstones. His works explore themes of horror, suspense, and the supernatural, blending vivid character development with gripping narratives. Some of his most famous novels include The Shining, It, Carrie, and Misery, many of which have been adapted into successful films and television series.	1947-09-21	\N	t
4	Gabriel	García Márquez	Scrittore colombiano, premio Nobel per la letteratura nel 1982. Autore del romanzo Cent'anni di solitudine, una delle opere più rappresentative del realismo magico.	1927-03-06	2014-04-17	f
5	Jane	Austen	Scrittrice britannica, famosa per romanzi come Orgoglio e pregiudizio e Ragione e sentimento. Le sue opere esplorano i costumi e le relazioni sociali dell'epoca.	1775-12-16	1817-07-18	f
24	Giacomo	Leopardi	È ritenuto tra i maggiori poeti italiani dell'Ottocento e una delle più importanti figure della letteratura mondiale, nonché uno dei principali esponenti del romanticismo letterario, sebbene abbia sempre criticato la corrente romantica di cui rifiutò quello che definiva "l'arido vero", ritenendosi vicino al classicismo. La profondità della sua riflessione sull'esistenza e sulla condizione umana — di ispirazione sensista e materialista — ne fa anche un filosofo di spessore. La qualità lirica della sua poesia lo ha reso un protagonista centrale nel panorama letterario e culturale internazionale, con ricadute che vanno molto oltre la sua epoca.	1798-06-29	1837-06-14	f
34	Mark	Twain	Mark Twain, born Samuel Langhorne Clemens, was an iconic American writer and humorist often referred to as the "Father of American Literature." He is best known for his novels The Adventures of Tom Sawyer and Adventures of Huckleberry Finn, which are celebrated for their vivid depictions of American life and sharp social critique. Twain's wit and storytelling continue to captivate readers around the world.	1835-11-30	1910-04-21	f
\.


--
-- TOC entry 3888 (class 0 OID 18700)
-- Dependencies: 232
-- Data for Name: book; Type: TABLE DATA; Schema: library; Owner: -
--

COPY library.book (isbn, title, blurb, publisher) FROM stdin;
9788807882356	Se una notte d'inverno un viaggiatore	In questo romanzo innovativo e affascinante, Italo Calvino invita il lettore a un viaggio labirintico attraverso dieci storie interrotte, ognuna appartenente a un genere e stile diverso. La narrazione segue "Tu," il lettore, che tenta di leggere un romanzo intitolato Se una notte d'inverno un viaggiatore, solo per essere trascinato in un mondo inaspettato di racconti interrotti, intrusioni d’autore e un mix di mistero e umorismo. L’opera di Calvino è una celebrazione della narrazione stessa, che esplora temi come l'identità, il linguaggio e l'arte della lettura.	Laterza
9780374296827	Zibaldone	Zibaldone is an extensive collection of Leopardi's personal notes, reflections, and essays, compiled over years. This monumental work delves into various subjects, including philosophy, literature, and human nature, offering deep insights into Leopardi's intellectual pursuits and the cultural milieu of his time.	Einaudi
9780385086950	Carrie	Stephen King's debut novel, 'Carrie,' tells the story of Carrie White, a high school girl with telekinetic powers who, after being relentlessly bullied, unleashes her wrath on her tormentors. This gripping tale explores themes of isolation, power, and revenge.	Feltrinelli
9788804484447	Il barone rampante	Cosimo Piovasco di Rondò, a dodici anni, decide di vivere per sempre sugli alberi. Da lì osserva il mondo e conduce una vita avventurosa e ribelle, tra storie d'amore, amicizie e battaglie, senza mai scendere a compromessi con la realtà. Un classico della letteratura italiana che fonde fantasia e filosofia.	Mondadori
9780060853983	Good Omens: The Nice and Accurate Prophecies of Agnes Nutter, Witch	Good Omens è una commedia apocalittica che narra l’inaspettata alleanza tra un angelo, Aziraphale, e un demone, Crowley, uniti per evitare l’Apocalisse. Entrambi hanno vissuto sulla Terra per millenni e si sono affezionati a essa, decidendo così di ostacolare il destino e contrastare i piani divini e infernali. Tra satira, humor britannico e profondi spunti filosofici, il romanzo esplora temi di moralità, libero arbitrio e amicizia. Frutto della collaborazione tra Neil Gaiman e Terry Pratchett, Good Omens è un classico della narrativa fantastica, adorato per il suo tono irriverente e la sua brillantezza narrativa.	Laterza
9788804727988	Cent'anni di solitudine	L'epica storia della famiglia Buendía nella mitica città di Macondo. Un capolavoro del realismo magico, che esplora temi di amore, solitudine e destino attraverso generazioni, mescolando il fantastico con la realtà della vita quotidiana.	Salani
9780552124751	The Colour of Magic	The inaugural novel of the Discworld series introduces readers to the flat, disc-shaped world supported by four elephants standing on the back of a giant turtle. The story follows the inept wizard Rincewind as he becomes the reluctant guide to Twoflower, Discworld's first tourist, leading to a series of humorous and perilous adventures.	Einaudi
9788807031334	L'amica geniale	Il primo romanzo della celebre "tetralogia napoletana", che segue l'amicizia complessa e profonda tra due donne, Elena e Lila, cresciute in un quartiere povero di Napoli. Il libro esplora l'infanzia e l'adolescenza delle protagoniste, sullo sfondo delle trasformazioni sociali italiane del dopoguerra.	Laterza
9788807722706	Norwegian Wood	Un romanzo che mescola nostalgia e introspezione, raccontando la storia di Toru Watanabe, che, ascoltando una canzone dei Beatles, ricorda i suoi anni giovanili, le amicizie e gli amori travagliati. Un ritratto toccante della solitudine e del dolore nella giovinezza.	Salani
9780375414273	Kafka on the shore	A surreal and captivating novel intertwining the lives of a teenage runaway and an elderly man with the ability to communicate with cats.	Einaudi
9788806228300	Il Fu Mattia Pascal	Mattia Pascal, creduto morto a causa di uno scambio di identità, decide di sfruttare l'occasione per rifarsi una vita sotto un nuovo nome. Tuttavia, scoprirà presto che vivere senza una vera identità è una condizione insostenibile. Questo romanzo esplora temi di identità, libertà e il conflitto tra apparenza e realtà.	Feltrinelli
9780552131063	Mort	In this fourth Discworld novel, Death takes on an apprentice named Mort. As Mort learns the ropes of ushering souls into the afterlife, he grapples with the moral complexities of his new role, especially when he tries to alter fate to save a princess, leading to unforeseen consequences.	Feltrinelli
9788807900142	Orgoglio e pregiudizio	Orgoglio e Pregiudizio è un romanzo classico che esplora temi come l'amore, il matrimonio, la classe sociale e l'importanza del giudizio. Ambientato nell'Inghilterra rurale del XIX secolo, il romanzo segue le vicende della famiglia Bennet.	Giunti
9788804668022	Le città invisibili	In questo libro, Marco Polo descrive a Kublai Khan una serie di città fantastiche, ognuna con caratteristiche uniche e surreali. Attraverso queste descrizioni, Calvino esplora temi come l'immaginazione, la memoria e il desiderio umano.	Laterza
9788817171461	I Malavoglia	"I Malavoglia" narra la storia di una famiglia di pescatori siciliani che affronta numerose avversità nel tentativo di migliorare la propria condizione sociale. Attraverso le vicende dei protagonisti, Verga esplora temi come la lotta per la sopravvivenza, l'attaccamento alle tradizioni e le sfide poste dal progresso.	Einaudi
\.


--
-- TOC entry 3880 (class 0 OID 18506)
-- Dependencies: 224
-- Data for Name: book_copy; Type: TABLE DATA; Schema: library; Owner: -
--

COPY library.book_copy (id, branch, book, removed) FROM stdin;
1335	34	9788807882356	f
1336	34	9788807882356	f
1337	34	9788807882356	f
1	5	9788804484447	t
6	3	9788807722706	f
7	3	9788807722706	f
8	4	9788807722706	f
9	7	9788807722706	f
10	8	9788807031334	f
11	8	9788807031334	f
12	9	9788807031334	f
13	9	9788807031334	f
14	9	9788807031334	f
15	9	9788807031334	f
16	9	9788807031334	f
17	5	9788807031334	f
18	5	9788807031334	f
19	3	9788804484447	f
20	3	9788804484447	f
21	3	9788804484447	f
22	9	9788804484447	f
1338	34	9788807882356	f
24	1	9788804484447	f
25	4	9788804484447	f
1339	34	9788807882356	f
27	7	9788804727988	f
28	3	9788804727988	f
29	4	9788804727988	f
30	5	9788804727988	f
31	9	9788807900142	f
32	1	9780060853983	f
1340	34	9788807882356	f
1341	34	9788807882356	f
1342	34	9788807882356	f
1343	34	9788807882356	f
1344	34	9788807882356	f
1267	3	9780060853983	f
36	8	9788804484447	f
37	8	9788804484447	f
38	8	9788804484447	f
43	7	9788804727988	f
44	7	9788804727988	f
26	7	9788807900142	t
1268	3	9780060853983	f
1269	3	9780060853983	f
1270	3	9780060853983	f
1271	3	9780060853983	f
1272	4	9780375414273	f
1273	4	9780375414273	f
1274	4	9780375414273	f
1275	4	9780375414273	f
1276	4	9780375414273	f
1345	1	9788807900142	f
1290	8	9780385086950	f
1291	8	9780385086950	f
1292	8	9780385086950	f
1346	1	9788807900142	f
1347	1	9788807900142	f
1348	1	9788807900142	f
1349	1	9788807900142	f
1277	1	9780374296827	f
1278	1	9780374296827	f
1279	1	9780374296827	f
1280	1	9780374296827	f
1281	1	9780374296827	f
1282	1	9780374296827	f
1283	1	9780374296827	f
1284	1	9780374296827	f
1285	1	9780374296827	f
1286	1	9780374296827	f
1350	9	9788807900142	f
1351	9	9788807900142	f
1352	9	9788807900142	f
1353	9	9788807900142	f
1354	9	9788807900142	f
1287	7	9788804484447	f
1288	7	9788804484447	f
1294	8	9780552131063	f
1355	3	9788807900142	f
1356	3	9788807900142	f
1295	3	9780552131063	f
1296	3	9780552131063	f
1297	3	9780552131063	f
1298	3	9780552131063	f
1299	3	9780552131063	f
1300	3	9780552131063	f
1301	3	9780552131063	f
1302	3	9780552131063	f
1303	3	9780552131063	f
1304	3	9780552131063	f
1357	5	9788807900142	f
1358	5	9788807900142	f
1305	5	9780375414273	f
1306	5	9780375414273	f
1307	5	9780375414273	f
1308	5	9780375414273	f
1309	5	9780375414273	f
1310	5	9780375414273	f
1311	5	9780375414273	f
1312	5	9780375414273	f
1313	5	9780375414273	f
1314	5	9780375414273	f
1359	35	9788804668022	t
1360	35	9788804668022	t
1361	35	9788804668022	t
1362	35	9788804668022	t
1363	35	9788804668022	t
1315	5	9780552124751	f
1316	5	9780552124751	f
1317	5	9780552124751	f
1318	5	9780552124751	f
1319	5	9780552124751	f
1364	38	9788817171461	f
1320	7	9780552124751	f
1321	7	9780552124751	f
1322	7	9780552124751	f
1323	7	9780552124751	f
1324	7	9780552124751	f
1325	4	9780552124751	f
1326	4	9780552124751	f
1327	4	9780552124751	f
1328	4	9780552124751	f
1329	4	9780552124751	f
1330	4	9780552124751	f
1331	4	9780552124751	f
1332	4	9780552124751	f
1260	5	9788804484447	f
\.


--
-- TOC entry 3876 (class 0 OID 18434)
-- Dependencies: 220
-- Data for Name: branch; Type: TABLE DATA; Schema: library; Owner: -
--

COPY library.branch (id, address, city, name) FROM stdin;
3	Piazza del sole 5	Milano	Biblioteca Aurora
9	Via della Fontana 2	Bergamo	Primo Levi
8	Via San Giorgio 13	Bergamo	Dante Alighieri
4	Via Cavour 1	Milano	BCF
1	Via dei Fiori 22	Milano	Filo di Arianna
7	Via dei Fiori 22	Como	Orizzonti
5	Via degli Angeli 6	Lecco	Alessandro Manzoni
34	Corso Martiri 23	Genova	Luisiana
35	Via Roma 3	Bologna	Letture
38	Via Galli 13	Napoli	Biblioteca centrale
\.


--
-- TOC entry 3889 (class 0 OID 18733)
-- Dependencies: 233
-- Data for Name: credits; Type: TABLE DATA; Schema: library; Owner: -
--

COPY library.credits (author, book) FROM stdin;
3	9788804484447
1	9788807031334
2	9788807722706
4	9788804727988
5	9788807900142
3	9788807882356
25	9780385086950
3	9788804668022
36	9788817171461
37	9788806228300
6	9780060853983
8	9780060853983
2	9780375414273
24	9780374296827
8	9780552124751
8	9780552131063
\.


--
-- TOC entry 3882 (class 0 OID 18524)
-- Dependencies: 226
-- Data for Name: loan; Type: TABLE DATA; Schema: library; Owner: -
--

COPY library.loan (start, patron, copy, due, returned, id) FROM stdin;
2024-12-17	684c9ae5-021f-4eba-8175-c50285b1ee76	26	2025-01-26	2024-12-17	14
2025-01-08	8534038d-b700-4cbf-8837-912740658c68	1281	2025-02-07	2025-01-08	20
2025-01-08	8534038d-b700-4cbf-8837-912740658c68	1280	2025-02-07	2025-01-08	19
2025-01-08	8534038d-b700-4cbf-8837-912740658c68	1279	2025-02-07	2025-01-08	18
2025-01-08	8534038d-b700-4cbf-8837-912740658c68	6	2025-02-07	2025-01-08	17
2025-01-08	8534038d-b700-4cbf-8837-912740658c68	1278	2025-02-19	2025-01-09	16
2025-01-09	8534038d-b700-4cbf-8837-912740658c68	10	2025-02-08	2025-01-09	21
2025-01-10	8534038d-b700-4cbf-8837-912740658c68	10	2025-02-09	2025-01-13	27
2025-01-10	8534038d-b700-4cbf-8837-912740658c68	19	2025-02-09	2025-01-13	25
2025-01-13	8534038d-b700-4cbf-8837-912740658c68	1287	2025-02-12	2025-01-13	30
2025-01-14	4e855cc4-bb56-443b-9ecf-82c02e761850	10	2025-02-13	2025-01-14	31
2025-01-10	8534038d-b700-4cbf-8837-912740658c68	6	2025-02-09	\N	24
2025-01-09	8534038d-b700-4cbf-8837-912740658c68	1272	2025-02-09	\N	22
2025-01-14	4e855cc4-bb56-443b-9ecf-82c02e761850	31	2025-02-13	2025-01-15	41
2025-01-14	4e855cc4-bb56-443b-9ecf-82c02e761850	10	2025-02-13	2025-01-15	39
2025-01-16	4e855cc4-bb56-443b-9ecf-82c02e761850	7	2025-02-15	\N	68
2025-01-16	5b7eef71-6077-4de9-bfdf-7c34d4b4e00b	1277	2025-02-15	\N	71
2025-01-16	4e855cc4-bb56-443b-9ecf-82c02e761850	1273	2025-01-01	2025-01-16	69
2025-01-18	217cd687-6c7d-41c1-bf16-8e78cf7d205d	1364	2025-02-17	\N	74
2025-01-16	4e855cc4-bb56-443b-9ecf-82c02e761850	1355	2025-02-20	2025-01-18	64
2025-01-16	4e855cc4-bb56-443b-9ecf-82c02e761850	32	2025-01-15	2025-01-18	65
2025-01-18	4e855cc4-bb56-443b-9ecf-82c02e761850	1273	2025-02-17	\N	76
2025-01-18	4e855cc4-bb56-443b-9ecf-82c02e761850	1335	2025-02-17	\N	77
2025-01-18	4e855cc4-bb56-443b-9ecf-82c02e761850	1260	2025-02-17	\N	78
2025-01-18	217cd687-6c7d-41c1-bf16-8e78cf7d205d	1267	2025-02-17	\N	80
2025-01-18	217cd687-6c7d-41c1-bf16-8e78cf7d205d	9	2025-02-17	\N	81
2025-01-18	217cd687-6c7d-41c1-bf16-8e78cf7d205d	1320	2025-02-17	\N	82
2025-01-18	8534038d-b700-4cbf-8837-912740658c68	1290	2025-02-17	\N	84
2025-01-18	8534038d-b700-4cbf-8837-912740658c68	1336	2025-02-17	\N	85
2025-01-18	d3e2c3e9-a153-4fb1-860b-f463226142f7	1357	2025-02-17	\N	88
2025-01-18	5b7eef71-6077-4de9-bfdf-7c34d4b4e00b	32	2025-02-17	\N	92
2025-01-18	5b7eef71-6077-4de9-bfdf-7c34d4b4e00b	1337	2025-02-17	2025-01-18	91
2025-01-18	217cd687-6c7d-41c1-bf16-8e78cf7d205d	1345	2025-02-17	2025-01-18	83
2024-12-17	684c9ae5-021f-4eba-8175-c50285b1ee76	17	2025-01-17	\N	98
2025-12-10	684c9ae5-021f-4eba-8175-c50285b1ee76	30	2025-01-10	\N	97
2024-12-04	d3e2c3e9-a153-4fb1-860b-f463226142f7	1305	2025-01-08	\N	87
2024-12-10	4e855cc4-bb56-443b-9ecf-82c02e761850	10	2025-01-15	\N	66
2024-12-01	d3e2c3e9-a153-4fb1-860b-f463226142f7	1291	2025-01-03	\N	89
2024-11-18	5b7eef71-6077-4de9-bfdf-7c34d4b4e00b	1294	2024-12-31	\N	96
\.


--
-- TOC entry 3884 (class 0 OID 18557)
-- Dependencies: 228
-- Data for Name: patron; Type: TABLE DATA; Schema: library; Owner: -
--

COPY library.patron ("user", tax_code, n_delays, category) FROM stdin;
4e855cc4-bb56-443b-9ecf-82c02e761850	LWWSMB00C11E009Q	0	premium
217cd687-6c7d-41c1-bf16-8e78cf7d205d	RSSMRA80F15H501X	0	premium
5b7eef71-6077-4de9-bfdf-7c34d4b4e00b	VRDLCU75R05F205T	0	base
684c9ae5-021f-4eba-8175-c50285b1ee76	LRESMB00C11E009W	0	base
8534038d-b700-4cbf-8837-912740658c68	BNCLSN85M01H501Y	0	premium
d3e2c3e9-a153-4fb1-860b-f463226142f7	MRTGLI88S60L736C	1	base
\.


--
-- TOC entry 3883 (class 0 OID 18551)
-- Dependencies: 227
-- Data for Name: patron_category; Type: TABLE DATA; Schema: library; Owner: -
--

COPY library.patron_category (name, loan_limit) FROM stdin;
base	3
premium	5
\.


--
-- TOC entry 3877 (class 0 OID 18456)
-- Dependencies: 221
-- Data for Name: publisher; Type: TABLE DATA; Schema: library; Owner: -
--

COPY library.publisher (name) FROM stdin;
Mondadori
Feltrinelli
Einaudi
Laterza
Salani
Giunti
Hachette Book Group
Macmillan Publishers
Treccani
\.


--
-- TOC entry 3885 (class 0 OID 18641)
-- Dependencies: 229
-- Data for Name: user; Type: TABLE DATA; Schema: library; Owner: -
--

COPY library."user" (id, email, password, first_name, last_name, type, removed) FROM stdin;
217cd687-6c7d-41c1-bf16-8e78cf7d205d	mariorossi@gmail.com	mariorossi	Mario	Rossi	patron	f
5b7eef71-6077-4de9-bfdf-7c34d4b4e00b	luca.verdi@example.com	luca.verdi@example.com	Luca	Verdi	patron	f
d3e2c3e9-a153-4fb1-860b-f463226142f7	giulia.moretti@example.com	giulia.moretti@example.com	Giulia	Moretti	patron	f
a4788b1b-9867-45bc-9bfc-44fc89a86b22	pinco@librarian.com	pinco@librarian.com	Pinco	Pallino	librarian	f
8534038d-b700-4cbf-8837-912740658c68	michele.bianchi@example.com	michele.bianchi@example.com	Michele	Bianchi	patron	f
684c9ae5-021f-4eba-8175-c50285b1ee76	christie@la.rue	christie	Christie	La Rue	patron	f
4e855cc4-bb56-443b-9ecf-82c02e761850	alessandro@gmail.com	alex11	Alessandro	Rossi	patron	f
437df57e-0ffe-47a4-a45c-a2cf85e1fea4	matilde@librarian.com	matilde	Matilde	Magistrali	librarian	f
\.


--
-- TOC entry 3903 (class 0 OID 0)
-- Dependencies: 230
-- Name: author_id_seq; Type: SEQUENCE SET; Schema: library; Owner: -
--

SELECT pg_catalog.setval('library.author_id_seq', 37, true);


--
-- TOC entry 3904 (class 0 OID 0)
-- Dependencies: 223
-- Name: book_copy_branch_seq; Type: SEQUENCE SET; Schema: library; Owner: -
--

SELECT pg_catalog.setval('library.book_copy_branch_seq', 1, false);


--
-- TOC entry 3905 (class 0 OID 0)
-- Dependencies: 222
-- Name: book_copy_id_seq; Type: SEQUENCE SET; Schema: library; Owner: -
--

SELECT pg_catalog.setval('library.book_copy_id_seq', 1364, true);


--
-- TOC entry 3906 (class 0 OID 0)
-- Dependencies: 219
-- Name: branch_id_seq; Type: SEQUENCE SET; Schema: library; Owner: -
--

SELECT pg_catalog.setval('library.branch_id_seq', 38, true);


--
-- TOC entry 3907 (class 0 OID 0)
-- Dependencies: 225
-- Name: loan_copy_seq; Type: SEQUENCE SET; Schema: library; Owner: -
--

SELECT pg_catalog.setval('library.loan_copy_seq', 3, true);


--
-- TOC entry 3908 (class 0 OID 0)
-- Dependencies: 234
-- Name: loan_id_seq; Type: SEQUENCE SET; Schema: library; Owner: -
--

SELECT pg_catalog.setval('library.loan_id_seq', 98, true);


--
-- TOC entry 3687 (class 2606 OID 18696)
-- Name: author author_pkey; Type: CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.author
    ADD CONSTRAINT author_pkey PRIMARY KEY (id);


--
-- TOC entry 3673 (class 2606 OID 18512)
-- Name: book_copy book_copy_pkey; Type: CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.book_copy
    ADD CONSTRAINT book_copy_pkey PRIMARY KEY (id);


--
-- TOC entry 3691 (class 2606 OID 18709)
-- Name: book book_pkey; Type: CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.book
    ADD CONSTRAINT book_pkey PRIMARY KEY (isbn);


--
-- TOC entry 3667 (class 2606 OID 18443)
-- Name: branch branch_address_city_key; Type: CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.branch
    ADD CONSTRAINT branch_address_city_key UNIQUE (address, city);


--
-- TOC entry 3669 (class 2606 OID 18441)
-- Name: branch branch_pkey; Type: CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.branch
    ADD CONSTRAINT branch_pkey PRIMARY KEY (id);


--
-- TOC entry 3693 (class 2606 OID 18737)
-- Name: credits credits_pkey; Type: CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.credits
    ADD CONSTRAINT credits_pkey PRIMARY KEY (author, book);


--
-- TOC entry 3675 (class 2606 OID 18973)
-- Name: loan loan_pkey; Type: CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.loan
    ADD CONSTRAINT loan_pkey PRIMARY KEY (id);


--
-- TOC entry 3677 (class 2606 OID 18556)
-- Name: patron_category patron_category_pkey; Type: CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.patron_category
    ADD CONSTRAINT patron_category_pkey PRIMARY KEY (name);


--
-- TOC entry 3679 (class 2606 OID 18563)
-- Name: patron patron_pkey; Type: CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.patron
    ADD CONSTRAINT patron_pkey PRIMARY KEY ("user");


--
-- TOC entry 3671 (class 2606 OID 18461)
-- Name: publisher publisher_pkey; Type: CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.publisher
    ADD CONSTRAINT publisher_pkey PRIMARY KEY (name);


--
-- TOC entry 3681 (class 2606 OID 18762)
-- Name: patron tax_code_unique; Type: CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.patron
    ADD CONSTRAINT tax_code_unique UNIQUE (tax_code);


--
-- TOC entry 3689 (class 2606 OID 27144)
-- Name: author unique_author_name; Type: CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.author
    ADD CONSTRAINT unique_author_name UNIQUE (first_name, last_name);


--
-- TOC entry 3683 (class 2606 OID 18652)
-- Name: user user_email_key; Type: CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library."user"
    ADD CONSTRAINT user_email_key UNIQUE (email);


--
-- TOC entry 3685 (class 2606 OID 18650)
-- Name: user user_pkey; Type: CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library."user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (id);


--
-- TOC entry 3706 (class 2620 OID 18814)
-- Name: loan au_loan_increment_patron_delay_counter; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER au_loan_increment_patron_delay_counter AFTER UPDATE ON library.loan FOR EACH ROW EXECUTE FUNCTION library.increment_patron_delay_counter();


--
-- TOC entry 3707 (class 2620 OID 27178)
-- Name: loan bd_loan_deny_deletion; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bd_loan_deny_deletion BEFORE DELETE ON library.loan FOR EACH ROW EXECUTE FUNCTION library.deny_deletion();


--
-- TOC entry 3717 (class 2620 OID 27177)
-- Name: patron bd_patron_deny_deletion; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bd_patron_deny_deletion BEFORE DELETE ON library.patron FOR EACH ROW EXECUTE FUNCTION library.deny_deletion();


--
-- TOC entry 3722 (class 2620 OID 27179)
-- Name: user bd_user_deny_deletion; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bd_user_deny_deletion BEFORE DELETE ON library."user" FOR EACH ROW EXECUTE FUNCTION library.deny_deletion();


--
-- TOC entry 3703 (class 2620 OID 18843)
-- Name: book_copy bi_book_copy_set_default_values; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bi_book_copy_set_default_values BEFORE INSERT ON library.book_copy FOR EACH ROW EXECUTE FUNCTION library.set_default_book_copy_values();


--
-- TOC entry 3708 (class 2620 OID 18778)
-- Name: loan bi_loan_check_copy_availability; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bi_loan_check_copy_availability BEFORE INSERT ON library.loan FOR EACH ROW EXECUTE FUNCTION library.check_copy_availability();


--
-- TOC entry 3709 (class 2620 OID 18772)
-- Name: loan bi_loan_check_delays; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bi_loan_check_delays BEFORE INSERT ON library.loan FOR EACH ROW EXECUTE FUNCTION library.check_patron_delays();


--
-- TOC entry 3710 (class 2620 OID 18782)
-- Name: loan bi_loan_check_patron_limit; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bi_loan_check_patron_limit BEFORE INSERT ON library.loan FOR EACH ROW EXECUTE FUNCTION library.check_patron_limit();


--
-- TOC entry 3711 (class 2620 OID 27141)
-- Name: loan bi_loan_deny_already_loaned_book; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bi_loan_deny_already_loaned_book BEFORE INSERT ON library.loan FOR EACH ROW EXECUTE FUNCTION library.deny_already_loaned_book();


--
-- TOC entry 3712 (class 2620 OID 18775)
-- Name: loan bi_loan_set_default_values; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bi_loan_set_default_values BEFORE INSERT ON library.loan FOR EACH ROW EXECUTE FUNCTION library.set_default_loan_values();


--
-- TOC entry 3718 (class 2620 OID 18933)
-- Name: patron bi_patron_check_referenced_user; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bi_patron_check_referenced_user BEFORE INSERT ON library.patron FOR EACH ROW EXECUTE FUNCTION library.check_referenced_user();


--
-- TOC entry 3719 (class 2620 OID 18833)
-- Name: patron bi_patron_set_default_values; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bi_patron_set_default_values BEFORE INSERT ON library.patron FOR EACH ROW EXECUTE FUNCTION library.set_default_patron_values();


--
-- TOC entry 3723 (class 2620 OID 18822)
-- Name: user bi_user_set_removed_to_false; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bi_user_set_removed_to_false BEFORE INSERT ON library."user" FOR EACH ROW EXECUTE FUNCTION library.set_removed_to_false();


--
-- TOC entry 3704 (class 2620 OID 27176)
-- Name: book_copy bu_book_copy_deny_update_if_removed; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bu_book_copy_deny_update_if_removed BEFORE UPDATE ON library.book_copy FOR EACH ROW EXECUTE FUNCTION library.book_copy_deny_update_if_removed();


--
-- TOC entry 3705 (class 2620 OID 18895)
-- Name: book_copy bu_book_copy_deny_update_on_loan; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bu_book_copy_deny_update_on_loan BEFORE UPDATE ON library.book_copy FOR EACH ROW EXECUTE FUNCTION library.deny_update_on_loan();


--
-- TOC entry 3713 (class 2620 OID 18804)
-- Name: loan bu_loan_check_if_loan_is_over; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bu_loan_check_if_loan_is_over BEFORE UPDATE ON library.loan FOR EACH ROW EXECUTE FUNCTION library.check_if_loan_is_over();


--
-- TOC entry 3714 (class 2620 OID 27169)
-- Name: loan bu_loan_check_return_date; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bu_loan_check_return_date BEFORE UPDATE ON library.loan FOR EACH ROW EXECUTE FUNCTION library.check_return_date();


--
-- TOC entry 3715 (class 2620 OID 27190)
-- Name: loan bu_loan_deny_unmodifiable_fields_update; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bu_loan_deny_unmodifiable_fields_update BEFORE UPDATE ON library.loan FOR EACH ROW EXECUTE FUNCTION library.deny_unmodifiable_fields_update();


--
-- TOC entry 3716 (class 2620 OID 27189)
-- Name: loan bu_loan_enforce_due_policy; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bu_loan_enforce_due_policy BEFORE UPDATE ON library.loan FOR EACH ROW EXECUTE FUNCTION library.enforce_due_policy();


--
-- TOC entry 3720 (class 2620 OID 27174)
-- Name: patron bu_patron_deny_update_if_removed; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bu_patron_deny_update_if_removed BEFORE UPDATE ON library.patron FOR EACH ROW EXECUTE FUNCTION library.patron_deny_update_if_removed();


--
-- TOC entry 3721 (class 2620 OID 18840)
-- Name: patron bu_patron_enforce_category_update_policy; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bu_patron_enforce_category_update_policy BEFORE UPDATE ON library.patron FOR EACH ROW EXECUTE FUNCTION library.enforce_category_update_policy();


--
-- TOC entry 3724 (class 2620 OID 18905)
-- Name: user bu_user_check_loans_before_removal; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bu_user_check_loans_before_removal BEFORE UPDATE ON library."user" FOR EACH ROW EXECUTE FUNCTION library.check_loans_before_removal();


--
-- TOC entry 3725 (class 2620 OID 18825)
-- Name: user bu_user_deny_unmodifiable_fields_update; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bu_user_deny_unmodifiable_fields_update BEFORE UPDATE ON library."user" FOR EACH ROW EXECUTE FUNCTION library.user_deny_unmodifiable_fields_update();


--
-- TOC entry 3726 (class 2620 OID 27172)
-- Name: user bu_user_deny_update_if_removed; Type: TRIGGER; Schema: library; Owner: -
--

CREATE TRIGGER bu_user_deny_update_if_removed BEFORE UPDATE ON library."user" FOR EACH ROW EXECUTE FUNCTION library.user_deny_update_if_removed();


--
-- TOC entry 3694 (class 2606 OID 18936)
-- Name: book_copy book_copy_branch_fkey; Type: FK CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.book_copy
    ADD CONSTRAINT book_copy_branch_fkey FOREIGN KEY (branch) REFERENCES library.branch(id);


--
-- TOC entry 3700 (class 2606 OID 18710)
-- Name: book book_publisher_fkey; Type: FK CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.book
    ADD CONSTRAINT book_publisher_fkey FOREIGN KEY (publisher) REFERENCES library.publisher(name);


--
-- TOC entry 3695 (class 2606 OID 18791)
-- Name: book_copy book_reference; Type: FK CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.book_copy
    ADD CONSTRAINT book_reference FOREIGN KEY (book) REFERENCES library.book(isbn);


--
-- TOC entry 3701 (class 2606 OID 18738)
-- Name: credits credits_author_fkey; Type: FK CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.credits
    ADD CONSTRAINT credits_author_fkey FOREIGN KEY (author) REFERENCES library.author(id);


--
-- TOC entry 3702 (class 2606 OID 18743)
-- Name: credits credits_book_fkey; Type: FK CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.credits
    ADD CONSTRAINT credits_book_fkey FOREIGN KEY (book) REFERENCES library.book(isbn);


--
-- TOC entry 3696 (class 2606 OID 18927)
-- Name: loan loan_copy_fkey; Type: FK CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.loan
    ADD CONSTRAINT loan_copy_fkey FOREIGN KEY (copy) REFERENCES library.book_copy(id);


--
-- TOC entry 3698 (class 2606 OID 18569)
-- Name: patron patron_category_fkey; Type: FK CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.patron
    ADD CONSTRAINT patron_category_fkey FOREIGN KEY (category) REFERENCES library.patron_category(name);


--
-- TOC entry 3697 (class 2606 OID 18798)
-- Name: loan patron_reference; Type: FK CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.loan
    ADD CONSTRAINT patron_reference FOREIGN KEY (patron) REFERENCES library.patron("user");


--
-- TOC entry 3699 (class 2606 OID 18786)
-- Name: patron user_id_reference; Type: FK CONSTRAINT; Schema: library; Owner: -
--

ALTER TABLE ONLY library.patron
    ADD CONSTRAINT user_id_reference FOREIGN KEY ("user") REFERENCES library."user"(id);


-- Completed on 2025-01-20 09:42:46 CET

--
-- PostgreSQL database dump complete
--

