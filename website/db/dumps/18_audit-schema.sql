--
-- PostgreSQL database dump
--

-- Dumped from database version 16.3 (Ubuntu 16.3-1.pgdg24.04+1)
-- Dumped by pg_dump version 16.3 (Ubuntu 16.3-1.pgdg24.04+1)

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
-- Name: audit; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA audit;


--
-- Name: audit_logs_id_seq; Type: SEQUENCE; Schema: audit; Owner: -
--

CREATE SEQUENCE audit.audit_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: actions_logs; Type: TABLE; Schema: audit; Owner: -
--

CREATE TABLE audit.actions_logs (
    log_datetime timestamp with time zone DEFAULT now() NOT NULL,
    event character varying,
    author bigint,
    ip inet,
    context json,
    id bigint DEFAULT nextval('audit.audit_logs_id_seq'::regclass) NOT NULL,
    session character varying(255) DEFAULT ''::character varying NOT NULL
);


--
-- Name: posts; Type: TABLE; Schema: audit; Owner: -
--

CREATE TABLE audit.posts (
    id integer NOT NULL,
    author bigint,
    ip inet NOT NULL,
    route character varying(256) NOT NULL,
    payload json NOT NULL,
    created_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    errors json,
    session character varying(255) DEFAULT ''::character varying NOT NULL,
    user_agent text
);


--
-- Name: posts_id_seq; Type: SEQUENCE; Schema: audit; Owner: -
--

CREATE SEQUENCE audit.posts_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: posts_id_seq; Type: SEQUENCE OWNED BY; Schema: audit; Owner: -
--

ALTER SEQUENCE audit.posts_id_seq OWNED BY audit.posts.id;


--
-- Name: posts id; Type: DEFAULT; Schema: audit; Owner: -
--

ALTER TABLE ONLY audit.posts ALTER COLUMN id SET DEFAULT nextval('audit.posts_id_seq'::regclass);


--
-- Name: actions_logs audit_logs_pkey; Type: CONSTRAINT; Schema: audit; Owner: -
--

ALTER TABLE ONLY audit.actions_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (id);


--
-- Name: posts posts_pkey; Type: CONSTRAINT; Schema: audit; Owner: -
--

ALTER TABLE ONLY audit.posts
    ADD CONSTRAINT posts_pkey PRIMARY KEY (id);


--
-- Name: audit_logs_index_event; Type: INDEX; Schema: audit; Owner: -
--

CREATE INDEX audit_logs_index_event ON audit.actions_logs USING btree (event);


--
-- Name: audit_logs_index_log_datetime; Type: INDEX; Schema: audit; Owner: -
--

CREATE INDEX audit_logs_index_log_datetime ON audit.actions_logs USING btree (log_datetime);


--
-- Name: posts_author; Type: INDEX; Schema: audit; Owner: -
--

CREATE INDEX posts_author ON audit.posts USING btree (author);


--
-- Name: posts_created_on_datetime; Type: INDEX; Schema: audit; Owner: -
--

CREATE INDEX posts_created_on_datetime ON audit.posts USING btree (created_on_datetime);


--
-- Name: posts_ip; Type: INDEX; Schema: audit; Owner: -
--

CREATE INDEX posts_ip ON audit.posts USING btree (ip);


--
-- Name: posts_route; Type: INDEX; Schema: audit; Owner: -
--

CREATE INDEX posts_route ON audit.posts USING btree (route);


--
-- PostgreSQL database dump complete
--

