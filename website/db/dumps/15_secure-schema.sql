--
-- PostgreSQL database dump
--

-- Dumped from database version 12.2 (Debian 12.2-2.pgdg100+1)
-- Dumped by pg_dump version 12.2 (Ubuntu 12.2-4)

-- Started on 2020-08-03 13:47:46 CEST

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
-- TOC entry 21 (class 2615 OID 514218)
-- Name: secure; Type: SCHEMA; Schema: -; Owner: geokrety
--

CREATE SCHEMA IF NOT EXISTS secure;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 243 (class 1259 OID 511637)
-- Name: gpg_keys; Type: TABLE; Schema: secure; Owner: geokrety
--

CREATE TABLE secure.gpg_keys (
    id integer NOT NULL,
    pubkey text NOT NULL,
    privatekey text NOT NULL
);


--
-- TOC entry 242 (class 1259 OID 511635)
-- Name: gpg_keys_id_seq; Type: SEQUENCE; Schema: secure; Owner: geokrety
--

CREATE SEQUENCE secure.gpg_keys_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 5673 (class 0 OID 0)
-- Dependencies: 242
-- Name: gpg_keys_id_seq; Type: SEQUENCE OWNED BY; Schema: secure; Owner: geokrety
--

ALTER SEQUENCE secure.gpg_keys_id_seq OWNED BY secure.gpg_keys.id;


--
-- TOC entry 5528 (class 2604 OID 511640)
-- Name: gpg_keys id; Type: DEFAULT; Schema: secure; Owner: geokrety
--

ALTER TABLE ONLY secure.gpg_keys ALTER COLUMN id SET DEFAULT nextval('secure.gpg_keys_id_seq'::regclass);


-- Completed on 2020-08-03 13:47:49 CEST

--
-- PostgreSQL database dump complete
--
