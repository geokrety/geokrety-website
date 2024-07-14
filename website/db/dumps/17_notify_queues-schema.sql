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
-- Name: notify_queues; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA notify_queues;


--
-- Name: amqp_notify_gkid(); Type: FUNCTION; Schema: notify_queues; Owner: -
--

CREATE FUNCTION notify_queues.amqp_notify_gkid() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_broker amqp.broker%rowtype;
BEGIN
    SELECT *
    FROM amqp.broker
    INTO v_broker
    WHERE broker_id = 1;

    IF FOUND THEN
        PERFORM amqp.publish(1, 'geokrety', '', json_build_object(
            'id', NEW.gkid,
            'op', TG_OP,
            'kind', TG_TABLE_NAME::text
        )::text);
    END IF;
    RETURN NEW;
END;
$$;


--
-- Name: amqp_notify_id(); Type: FUNCTION; Schema: notify_queues; Owner: -
--

CREATE FUNCTION notify_queues.amqp_notify_id() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_broker amqp.broker%rowtype;
BEGIN
    SELECT *
    FROM amqp.broker
    INTO v_broker
    WHERE broker_id = 1;

    IF FOUND THEN
        PERFORM amqp.publish(1, 'geokrety', '', json_build_object(
            'id', NEW.id,
            'op', TG_OP,
            'kind', TG_TABLE_NAME::text
        )::text);
    END IF;
    RETURN NEW;
END;
$$;


--
-- Name: channel_notify(); Type: FUNCTION; Schema: notify_queues; Owner: -
--

CREATE FUNCTION notify_queues.channel_notify() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    PERFORM pg_notify(NEW.channel, NEW.payload::text);
    RETURN NEW;
END;
$$;


--
-- Name: new_handle(); Type: FUNCTION; Schema: notify_queues; Owner: -
--

CREATE FUNCTION notify_queues.new_handle() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    INSERT INTO notify_queues.geokrety_changes ("channel", "action", "payload")
	VALUES (TG_TABLE_NAME, TG_OP, COALESCE(NEW.id, OLD.id));
    RETURN NEW;
END;
$$;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: geokrety_changes; Type: TABLE; Schema: notify_queues; Owner: -
--

CREATE TABLE notify_queues.geokrety_changes (
    id integer NOT NULL,
    channel character varying(64) NOT NULL,
    action character varying(64) NOT NULL,
    payload bigint NOT NULL,
    created_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    processed_on_datetime timestamp with time zone,
    errors json
);


--
-- Name: geokrety_changes_id_seq; Type: SEQUENCE; Schema: notify_queues; Owner: -
--

CREATE SEQUENCE notify_queues.geokrety_changes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: geokrety_changes_id_seq; Type: SEQUENCE OWNED BY; Schema: notify_queues; Owner: -
--

ALTER SEQUENCE notify_queues.geokrety_changes_id_seq OWNED BY notify_queues.geokrety_changes.id;


--
-- Name: geokrety_changes id; Type: DEFAULT; Schema: notify_queues; Owner: -
--

ALTER TABLE ONLY notify_queues.geokrety_changes ALTER COLUMN id SET DEFAULT nextval('notify_queues.geokrety_changes_id_seq'::regclass);


--
-- Name: geokrety_changes geokrety_changes_pkey; Type: CONSTRAINT; Schema: notify_queues; Owner: -
--

ALTER TABLE ONLY notify_queues.geokrety_changes
    ADD CONSTRAINT geokrety_changes_pkey PRIMARY KEY (id);


--
-- Name: geokrety_changes_processed_on_datetime; Type: INDEX; Schema: notify_queues; Owner: -
--

CREATE INDEX geokrety_changes_processed_on_datetime ON notify_queues.geokrety_changes USING btree (processed_on_datetime);


--
-- Name: geokrety_changes channel_notify; Type: TRIGGER; Schema: notify_queues; Owner: -
--

CREATE TRIGGER channel_notify AFTER INSERT ON notify_queues.geokrety_changes FOR EACH ROW EXECUTE FUNCTION notify_queues.channel_notify();


--
-- PostgreSQL database dump complete
--

