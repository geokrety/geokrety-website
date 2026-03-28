--
-- PostgreSQL database dump
--

\restrict QlNNfy1mpLL3eZfimg3mK31dJVozwVfpwCUEVKocyjn7yoEWgaDgccW5LG5ekit

-- Dumped from database version 16.13 (Ubuntu 16.13-1.pgdg24.04+1)
-- Dumped by pg_dump version 18.3 (Ubuntu 18.3-1.pgdg22.04+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: secure; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA secure;
CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: gpg_keys; Type: TABLE; Schema: secure; Owner: -
--

CREATE TABLE secure.gpg_keys (
    id integer NOT NULL,
    pubkey text NOT NULL,
    privatekey text NOT NULL
);


--
-- Name: gpg_keys_id_seq; Type: SEQUENCE; Schema: secure; Owner: -
--

CREATE SEQUENCE secure.gpg_keys_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gpg_keys_id_seq; Type: SEQUENCE OWNED BY; Schema: secure; Owner: -
--

ALTER SEQUENCE secure.gpg_keys_id_seq OWNED BY secure.gpg_keys.id;


--
-- Name: gpg_keys id; Type: DEFAULT; Schema: secure; Owner: -
--

ALTER TABLE ONLY secure.gpg_keys ALTER COLUMN id SET DEFAULT nextval('secure.gpg_keys_id_seq'::regclass);


--
-- PostgreSQL database dump complete
--

\unrestrict QlNNfy1mpLL3eZfimg3mK31dJVozwVfpwCUEVKocyjn7yoEWgaDgccW5LG5ekit

