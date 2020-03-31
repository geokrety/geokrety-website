--
-- PostgreSQL database dump
--

-- Dumped from database version 12.2 (Debian 12.2-2.pgdg100+1)
-- Dumped by pg_dump version 12.1

-- Started on 2020-03-31 19:52:57 GMT

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
-- TOC entry 12 (class 2615 OID 16385)
-- Name: geokrety; Type: SCHEMA; Schema: -; Owner: geokrety
--

CREATE SCHEMA geokrety;


ALTER SCHEMA geokrety OWNER TO geokrety;

--
-- TOC entry 270 (class 1255 OID 91283)
-- Name: on_update_current_timestamp(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.on_update_current_timestamp() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN NEW.updated_on_datetime = now(); RETURN NEW; END;$$;


ALTER FUNCTION geokrety.on_update_current_timestamp() OWNER TO geokrety;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 208 (class 1259 OID 20969)
-- Name: gk_account_activation; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_account_activation (
    id bigint NOT NULL,
    token character varying(60) NOT NULL,
    "user" bigint NOT NULL,
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    used_on_datetime timestamp(0) with time zone,
    requesting_ip inet NOT NULL,
    validating_ip inet,
    used smallint DEFAULT '0'::smallint NOT NULL,
    CONSTRAINT validate_used CHECK ((used = ANY (ARRAY[0, 1, 2])))
);


ALTER TABLE geokrety.gk_account_activation OWNER TO geokrety;

--
-- TOC entry 3331 (class 0 OID 0)
-- Dependencies: 208
-- Name: COLUMN gk_account_activation.used; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_account_activation.used IS '0=unused 1=validated 2=expired';


--
-- TOC entry 207 (class 1259 OID 20967)
-- Name: account_activation_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.account_activation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.account_activation_id_seq OWNER TO geokrety;

--
-- TOC entry 3332 (class 0 OID 0)
-- Dependencies: 207
-- Name: account_activation_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.account_activation_id_seq OWNED BY geokrety.gk_account_activation.id;


--
-- TOC entry 210 (class 1259 OID 20984)
-- Name: gk_badges; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_badges (
    id bigint NOT NULL,
    holder bigint NOT NULL,
    description character varying(128) NOT NULL,
    filename character varying(32) NOT NULL,
    awarded_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE geokrety.gk_badges OWNER TO geokrety;

--
-- TOC entry 209 (class 1259 OID 20982)
-- Name: badges_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.badges_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.badges_id_seq OWNER TO geokrety;

--
-- TOC entry 3333 (class 0 OID 0)
-- Dependencies: 209
-- Name: badges_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.badges_id_seq OWNED BY geokrety.gk_badges.id;


--
-- TOC entry 212 (class 1259 OID 20991)
-- Name: gk_email_activation; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_email_activation (
    id bigint NOT NULL,
    token character varying(60) NOT NULL,
    revert_token character varying(60) NOT NULL,
    "user" bigint NOT NULL,
    previous_email character varying(150),
    email character varying(150) NOT NULL,
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP,
    used_on_datetime timestamp(0) with time zone,
    reverted_on_datetime timestamp(0) with time zone,
    requesting_ip inet NOT NULL,
    updating_ip inet,
    reverting_ip inet,
    used smallint DEFAULT '0'::smallint NOT NULL,
    CONSTRAINT validate_used CHECK ((used = ANY (ARRAY[0, 1, 2, 3, 4, 5, 6])))
);


ALTER TABLE geokrety.gk_email_activation OWNER TO geokrety;

--
-- TOC entry 3334 (class 0 OID 0)
-- Dependencies: 212
-- Name: COLUMN gk_email_activation.previous_email; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_email_activation.previous_email IS 'Store the previous in case of needed rollback';


--
-- TOC entry 3335 (class 0 OID 0)
-- Dependencies: 212
-- Name: COLUMN gk_email_activation.used; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_email_activation.used IS '0=unused 1=validated 2=refused 3=expired';


--
-- TOC entry 211 (class 1259 OID 20989)
-- Name: email_activation_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.email_activation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.email_activation_id_seq OWNER TO geokrety;

--
-- TOC entry 3336 (class 0 OID 0)
-- Dependencies: 211
-- Name: email_activation_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.email_activation_id_seq OWNED BY geokrety.gk_email_activation.id;


--
-- TOC entry 214 (class 1259 OID 21002)
-- Name: gk_geokrety; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_geokrety (
    id bigint NOT NULL,
    gkid bigint NOT NULL,
    tracking_code character varying(9) NOT NULL,
    name character varying(75) NOT NULL,
    mission text,
    owner bigint,
    distance bigint DEFAULT '0'::bigint NOT NULL,
    caches_count integer DEFAULT 0 NOT NULL,
    pictures_count smallint DEFAULT '0'::smallint NOT NULL,
    last_position bigint,
    last_log bigint,
    holder bigint,
    avatar bigint,
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP,
    missing boolean DEFAULT false NOT NULL,
    type smallint NOT NULL,
    CONSTRAINT validate_type CHECK ((type = ANY (ARRAY[0, 1, 2, 3, 4])))
);


ALTER TABLE geokrety.gk_geokrety OWNER TO geokrety;

--
-- TOC entry 3337 (class 0 OID 0)
-- Dependencies: 214
-- Name: COLUMN gk_geokrety.gkid; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_geokrety.gkid IS 'The real GK id : https://stackoverflow.com/a/33791018/944936';


--
-- TOC entry 3338 (class 0 OID 0)
-- Dependencies: 214
-- Name: COLUMN gk_geokrety.holder; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_geokrety.holder IS 'In the hands of user';


--
-- TOC entry 3339 (class 0 OID 0)
-- Dependencies: 214
-- Name: COLUMN gk_geokrety.missing; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_geokrety.missing IS 'true=missing';


--
-- TOC entry 3340 (class 0 OID 0)
-- Dependencies: 214
-- Name: COLUMN gk_geokrety.type; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_geokrety.type IS '0, 1, 2, 3, 4';


--
-- TOC entry 213 (class 1259 OID 21000)
-- Name: geokrety_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.geokrety_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.geokrety_id_seq OWNER TO geokrety;

--
-- TOC entry 3341 (class 0 OID 0)
-- Dependencies: 213
-- Name: geokrety_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.geokrety_id_seq OWNED BY geokrety.gk_geokrety.id;


--
-- TOC entry 216 (class 1259 OID 21016)
-- Name: gk_geokrety_rating; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_geokrety_rating (
    id bigint NOT NULL,
    geokret bigint NOT NULL,
    author bigint NOT NULL,
    rate smallint NOT NULL,
    rated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE geokrety.gk_geokrety_rating OWNER TO geokrety;

--
-- TOC entry 3342 (class 0 OID 0)
-- Dependencies: 216
-- Name: COLUMN gk_geokrety_rating.rate; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_geokrety_rating.rate IS 'single rating (number of stars)';


--
-- TOC entry 215 (class 1259 OID 21014)
-- Name: geokrety_rating_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.geokrety_rating_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.geokrety_rating_id_seq OWNER TO geokrety;

--
-- TOC entry 3343 (class 0 OID 0)
-- Dependencies: 215
-- Name: geokrety_rating_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.geokrety_rating_id_seq OWNED BY geokrety.gk_geokrety_rating.id;


--
-- TOC entry 218 (class 1259 OID 21024)
-- Name: gk_mails; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_mails (
    id bigint NOT NULL,
    token character varying(10) NOT NULL,
    from_user bigint,
    to_user bigint,
    subject character varying(255) NOT NULL,
    content text NOT NULL,
    sent_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    ip inet NOT NULL
);


ALTER TABLE geokrety.gk_mails OWNER TO geokrety;

--
-- TOC entry 222 (class 1259 OID 21044)
-- Name: gk_moves; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_moves (
    id bigint NOT NULL,
    geokret bigint NOT NULL,
    lat double precision,
    lon double precision,
    alt integer DEFAULT '-32768'::integer,
    country character varying(3),
    distance bigint,
    waypoint character varying(11),
    author bigint,
    comment character varying(5120),
    pictures_count smallint DEFAULT '0'::smallint NOT NULL,
    comments_count integer DEFAULT 0 NOT NULL,
    username character varying(80),
    app character varying(16),
    app_ver character varying(128),
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    moved_on_datetime timestamp(0) with time zone,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP,
    logtype smallint NOT NULL,
    CONSTRAINT validate_logtype CHECK ((logtype = ANY (ARRAY[0, 1, 2, 3, 4, 5, 6])))
);


ALTER TABLE geokrety.gk_moves OWNER TO geokrety;

--
-- TOC entry 3344 (class 0 OID 0)
-- Dependencies: 222
-- Name: COLUMN gk_moves.alt; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_moves.alt IS '-32768 when alt cannot be found';


--
-- TOC entry 3345 (class 0 OID 0)
-- Dependencies: 222
-- Name: COLUMN gk_moves.country; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_moves.country IS 'ISO 3166-1 https://fr.wikipedia.org/wiki/ISO_3166-1';


--
-- TOC entry 3346 (class 0 OID 0)
-- Dependencies: 222
-- Name: COLUMN gk_moves.app; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_moves.app IS 'source of the log';


--
-- TOC entry 3347 (class 0 OID 0)
-- Dependencies: 222
-- Name: COLUMN gk_moves.app_ver; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_moves.app_ver IS 'application version/codename';


--
-- TOC entry 3348 (class 0 OID 0)
-- Dependencies: 222
-- Name: COLUMN gk_moves.moved_on_datetime; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_moves.moved_on_datetime IS 'The move as configured by user';


--
-- TOC entry 3349 (class 0 OID 0)
-- Dependencies: 222
-- Name: COLUMN gk_moves.logtype; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_moves.logtype IS '0=drop, 1=grab, 2=comment, 3=met, 4=arch, 5=dip';


--
-- TOC entry 220 (class 1259 OID 21034)
-- Name: gk_moves_comments; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_moves_comments (
    id bigint NOT NULL,
    move bigint NOT NULL,
    geokret bigint NOT NULL,
    author bigint,
    content character varying(500) NOT NULL,
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP,
    type smallint NOT NULL,
    CONSTRAINT validate_type CHECK ((type = ANY (ARRAY[0, 1])))
);


ALTER TABLE geokrety.gk_moves_comments OWNER TO geokrety;

--
-- TOC entry 3350 (class 0 OID 0)
-- Dependencies: 220
-- Name: COLUMN gk_moves_comments.type; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_moves_comments.type IS '0=comment, 1=missing';


--
-- TOC entry 224 (class 1259 OID 21058)
-- Name: gk_news; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_news (
    id bigint NOT NULL,
    title character varying(128) NOT NULL,
    content text,
    author_name character varying(80),
    author bigint,
    comments_count integer DEFAULT 0 NOT NULL,
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    last_commented_on_datetime timestamp(0) with time zone
);


ALTER TABLE geokrety.gk_news OWNER TO geokrety;

--
-- TOC entry 226 (class 1259 OID 21069)
-- Name: gk_news_comments; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_news_comments (
    id bigint NOT NULL,
    news bigint NOT NULL,
    author bigint NOT NULL,
    content character varying(1000) NOT NULL,
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE geokrety.gk_news_comments OWNER TO geokrety;

--
-- TOC entry 228 (class 1259 OID 21079)
-- Name: gk_news_comments_access; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_news_comments_access (
    id bigint NOT NULL,
    news bigint NOT NULL,
    author bigint NOT NULL,
    last_read_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    subscribed boolean NOT NULL
);


ALTER TABLE geokrety.gk_news_comments_access OWNER TO geokrety;

--
-- TOC entry 230 (class 1259 OID 21085)
-- Name: gk_owner_codes; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_owner_codes (
    id bigint NOT NULL,
    geokret bigint NOT NULL,
    token character varying(20) NOT NULL,
    generated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    claimed_on_datetime timestamp(0) with time zone,
    "user" bigint
);


ALTER TABLE geokrety.gk_owner_codes OWNER TO geokrety;

--
-- TOC entry 232 (class 1259 OID 21092)
-- Name: gk_password_tokens; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_password_tokens (
    id bigint NOT NULL,
    token character varying(60) NOT NULL,
    "user" bigint NOT NULL,
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    used_on_datetime timestamp(0) with time zone,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP,
    requesting_ip inet NOT NULL,
    used smallint NOT NULL,
    CONSTRAINT validate_used CHECK ((used = ANY (ARRAY[0, 1])))
);


ALTER TABLE geokrety.gk_password_tokens OWNER TO geokrety;

--
-- TOC entry 3351 (class 0 OID 0)
-- Dependencies: 232
-- Name: COLUMN gk_password_tokens.used; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_password_tokens.used IS '0=unused 1=used';


--
-- TOC entry 234 (class 1259 OID 21100)
-- Name: gk_pictures; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_pictures (
    id bigint NOT NULL,
    author bigint,
    bucket character varying(128),
    key character varying(128),
    move bigint,
    geokret bigint,
    "user" bigint,
    filename character varying(256),
    caption character varying(128),
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP,
    uploaded_on_datetime timestamp(0) with time zone,
    type smallint NOT NULL,
    CONSTRAINT validate_type CHECK ((type = ANY (ARRAY[0, 1, 2])))
);


ALTER TABLE geokrety.gk_pictures OWNER TO geokrety;

--
-- TOC entry 3352 (class 0 OID 0)
-- Dependencies: 234
-- Name: COLUMN gk_pictures.type; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_pictures.type IS 'const PICTURE_GEOKRET_AVATAR = 0; const PICTURE_GEOKRET_MOVE = 1; const PICTURE_USER_AVATAR = 2;';


--
-- TOC entry 236 (class 1259 OID 21114)
-- Name: gk_races; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_races (
    id bigint NOT NULL,
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP,
    organizer bigint,
    private boolean DEFAULT false NOT NULL,
    password character varying(16),
    title character varying(128) NOT NULL,
    description text NOT NULL,
    start_on_datetime timestamp(0) with time zone NOT NULL,
    end_on_datetime timestamp(0) with time zone NOT NULL,
    target_dist bigint,
    target_caches bigint,
    waypoint character varying(11),
    target_lat double precision,
    target_lon double precision,
    type character varying(32) NOT NULL,
    status smallint DEFAULT '0'::smallint NOT NULL,
    CONSTRAINT validate_status CHECK ((status = ANY (ARRAY[0, 1, 2, 3]))),
    CONSTRAINT validate_type CHECK (((type)::text = ANY ((ARRAY['wpt'::character varying, 'targetDistance'::character varying, 'targetCaches'::character varying, 'maxDistance'::character varying, 'maxCaches'::character varying])::text[])))
);


ALTER TABLE geokrety.gk_races OWNER TO geokrety;

--
-- TOC entry 3353 (class 0 OID 0)
-- Dependencies: 236
-- Name: COLUMN gk_races.created_on_datetime; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_races.created_on_datetime IS 'Creation date';


--
-- TOC entry 3354 (class 0 OID 0)
-- Dependencies: 236
-- Name: COLUMN gk_races.private; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_races.private IS '0 = public, 1 = private';


--
-- TOC entry 3355 (class 0 OID 0)
-- Dependencies: 236
-- Name: COLUMN gk_races.password; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_races.password IS 'password to join the race';


--
-- TOC entry 3356 (class 0 OID 0)
-- Dependencies: 236
-- Name: COLUMN gk_races.start_on_datetime; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_races.start_on_datetime IS 'Race start date';


--
-- TOC entry 3357 (class 0 OID 0)
-- Dependencies: 236
-- Name: COLUMN gk_races.end_on_datetime; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_races.end_on_datetime IS 'Race end date';


--
-- TOC entry 3358 (class 0 OID 0)
-- Dependencies: 236
-- Name: COLUMN gk_races.target_dist; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_races.target_dist IS 'target distance';


--
-- TOC entry 3359 (class 0 OID 0)
-- Dependencies: 236
-- Name: COLUMN gk_races.target_caches; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_races.target_caches IS 'targeted number of caches';


--
-- TOC entry 3360 (class 0 OID 0)
-- Dependencies: 236
-- Name: COLUMN gk_races.status; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_races.status IS 'race status. 0 = initialized, 1 = persist, 2 = finite, 3 = finished but logs flow down';


--
-- TOC entry 238 (class 1259 OID 21125)
-- Name: gk_races_participants; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_races_participants (
    id bigint NOT NULL,
    race bigint NOT NULL,
    geokret bigint NOT NULL,
    initial_distance bigint NOT NULL,
    initial_caches_count integer NOT NULL,
    distance_to_destination bigint,
    joined_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP,
    finished_on_datetime timestamp(0) with time zone,
    finish_distance bigint NOT NULL,
    finish_caches_count integer NOT NULL,
    finish_lat double precision,
    finish_lon double precision
);


ALTER TABLE geokrety.gk_races_participants OWNER TO geokrety;

--
-- TOC entry 255 (class 1259 OID 156207)
-- Name: gk_statistics_counters; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_statistics_counters (
    id integer NOT NULL,
    name character varying(32) NOT NULL,
    value double precision NOT NULL
);


ALTER TABLE geokrety.gk_statistics_counters OWNER TO geokrety;

--
-- TOC entry 254 (class 1259 OID 156205)
-- Name: gk_statistics_counters_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.gk_statistics_counters_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.gk_statistics_counters_id_seq OWNER TO geokrety;

--
-- TOC entry 3361 (class 0 OID 0)
-- Dependencies: 254
-- Name: gk_statistics_counters_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.gk_statistics_counters_id_seq OWNED BY geokrety.gk_statistics_counters.id;


--
-- TOC entry 253 (class 1259 OID 155222)
-- Name: gk_statistics_daily_counters; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_statistics_daily_counters (
    id integer NOT NULL,
    date date NOT NULL,
    day_total integer NOT NULL,
    geokrety_created integer NOT NULL,
    geokrety_created_total integer NOT NULL,
    geokrety_in_caches integer NOT NULL,
    percentage_in_caches double precision NOT NULL,
    users_registered integer NOT NULL,
    users_registered_total integer NOT NULL,
    moves_created bigint NOT NULL,
    moves_created_total bigint NOT NULL
);


ALTER TABLE geokrety.gk_statistics_daily_counters OWNER TO geokrety;

--
-- TOC entry 252 (class 1259 OID 155220)
-- Name: gk_statistics_daily_counters_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.gk_statistics_daily_counters_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.gk_statistics_daily_counters_id_seq OWNER TO geokrety;

--
-- TOC entry 3362 (class 0 OID 0)
-- Dependencies: 252
-- Name: gk_statistics_daily_counters_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.gk_statistics_daily_counters_id_seq OWNED BY geokrety.gk_statistics_daily_counters.id;


--
-- TOC entry 240 (class 1259 OID 21135)
-- Name: gk_users; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_users (
    id bigint NOT NULL,
    username character varying(80) NOT NULL,
    password character varying(120),
    email character varying(150),
    joined_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP,
    daily_mails boolean DEFAULT true NOT NULL,
    registration_ip inet NOT NULL,
    preferred_language character varying(2),
    home_latitude double precision,
    home_longitude double precision,
    observation_area smallint,
    home_country character varying,
    daily_mails_hour smallint DEFAULT '7'::smallint NOT NULL,
    avatar bigint,
    pictures_count integer DEFAULT 0 NOT NULL,
    last_mail_datetime timestamp(0) with time zone,
    last_login_datetime timestamp(0) with time zone,
    terms_of_use_datetime timestamp(0) with time zone,
    secid character varying(128) NOT NULL,
    statpic_template integer DEFAULT 1 NOT NULL,
    email_invalid smallint DEFAULT '0'::smallint NOT NULL,
    account_valid smallint DEFAULT '0'::smallint NOT NULL,
    CONSTRAINT validate_account_valid CHECK ((account_valid = ANY (ARRAY[0, 1]))),
    CONSTRAINT validate_email_invalid CHECK ((email_invalid = ANY (ARRAY[0, 1, 2])))
);


ALTER TABLE geokrety.gk_users OWNER TO geokrety;

--
-- TOC entry 3363 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN gk_users.pictures_count; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_users.pictures_count IS 'Attached avatar count';


--
-- TOC entry 3364 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN gk_users.terms_of_use_datetime; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_users.terms_of_use_datetime IS 'Acceptation date';


--
-- TOC entry 3365 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN gk_users.secid; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_users.secid IS 'connect by other applications';


--
-- TOC entry 3366 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN gk_users.account_valid; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_users.account_valid IS '0=unconfirmed 1=confirmed';


--
-- TOC entry 242 (class 1259 OID 21153)
-- Name: gk_watched; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_watched (
    id bigint NOT NULL,
    "user" bigint NOT NULL,
    geokret bigint NOT NULL,
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE geokrety.gk_watched OWNER TO geokrety;

--
-- TOC entry 244 (class 1259 OID 21160)
-- Name: gk_waypoints; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_waypoints (
    id bigint NOT NULL,
    waypoint character varying(11) NOT NULL,
    lat double precision,
    lon double precision,
    alt integer DEFAULT '-32768'::integer NOT NULL,
    country character varying,
    name character varying(255),
    owner character varying(150),
    type character varying(200),
    country_name character varying(200),
    link character varying(255),
    added_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP,
    status smallint DEFAULT '1'::smallint NOT NULL,
    CONSTRAINT validate_status CHECK ((status = ANY (ARRAY[0, 1, 2, 3, 6, 7])))
);


ALTER TABLE geokrety.gk_waypoints OWNER TO geokrety;

--
-- TOC entry 3367 (class 0 OID 0)
-- Dependencies: 244
-- Name: COLUMN gk_waypoints.country; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_waypoints.country IS 'country code as ISO 3166-1 alpha-2';


--
-- TOC entry 3368 (class 0 OID 0)
-- Dependencies: 244
-- Name: COLUMN gk_waypoints.country_name; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_waypoints.country_name IS 'full English country name';


--
-- TOC entry 3369 (class 0 OID 0)
-- Dependencies: 244
-- Name: COLUMN gk_waypoints.status; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_waypoints.status IS '0, 1, 2, 3, 6, 7';


--
-- TOC entry 245 (class 1259 OID 21171)
-- Name: gk_waypoints_country; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_waypoints_country (
    original character varying(191) NOT NULL,
    country character varying(191)
);


ALTER TABLE geokrety.gk_waypoints_country OWNER TO geokrety;

--
-- TOC entry 257 (class 1259 OID 157047)
-- Name: waypoints_gc_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.waypoints_gc_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.waypoints_gc_id_seq OWNER TO geokrety;

--
-- TOC entry 256 (class 1259 OID 156373)
-- Name: gk_waypoints_gc; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_waypoints_gc (
    id bigint DEFAULT nextval('geokrety.waypoints_gc_id_seq'::regclass) NOT NULL,
    waypoint character varying(11) NOT NULL,
    coordinates point NOT NULL,
    country character varying(3) NOT NULL,
    elevation integer NOT NULL
);


ALTER TABLE geokrety.gk_waypoints_gc OWNER TO geokrety;

--
-- TOC entry 246 (class 1259 OID 21174)
-- Name: gk_waypoints_sync; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_waypoints_sync (
    service_id character varying(5) NOT NULL,
    last_update character varying(15)
);


ALTER TABLE geokrety.gk_waypoints_sync OWNER TO geokrety;

--
-- TOC entry 3370 (class 0 OID 0)
-- Dependencies: 246
-- Name: TABLE gk_waypoints_sync; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON TABLE geokrety.gk_waypoints_sync IS 'Last synchronization time for GC services';


--
-- TOC entry 247 (class 1259 OID 21177)
-- Name: gk_waypoints_types; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_waypoints_types (
    type character varying(255) NOT NULL,
    cache_type character varying(255)
);


ALTER TABLE geokrety.gk_waypoints_types OWNER TO geokrety;

--
-- TOC entry 217 (class 1259 OID 21022)
-- Name: mails_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.mails_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.mails_id_seq OWNER TO geokrety;

--
-- TOC entry 3371 (class 0 OID 0)
-- Dependencies: 217
-- Name: mails_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.mails_id_seq OWNED BY geokrety.gk_mails.id;


--
-- TOC entry 219 (class 1259 OID 21032)
-- Name: move_comments_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.move_comments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.move_comments_id_seq OWNER TO geokrety;

--
-- TOC entry 3372 (class 0 OID 0)
-- Dependencies: 219
-- Name: move_comments_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.move_comments_id_seq OWNED BY geokrety.gk_moves_comments.id;


--
-- TOC entry 221 (class 1259 OID 21042)
-- Name: moves_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.moves_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.moves_id_seq OWNER TO geokrety;

--
-- TOC entry 3373 (class 0 OID 0)
-- Dependencies: 221
-- Name: moves_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.moves_id_seq OWNED BY geokrety.gk_moves.id;


--
-- TOC entry 227 (class 1259 OID 21077)
-- Name: news_comments_access_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.news_comments_access_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.news_comments_access_id_seq OWNER TO geokrety;

--
-- TOC entry 3374 (class 0 OID 0)
-- Dependencies: 227
-- Name: news_comments_access_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.news_comments_access_id_seq OWNED BY geokrety.gk_news_comments_access.id;


--
-- TOC entry 225 (class 1259 OID 21067)
-- Name: news_comments_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.news_comments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.news_comments_id_seq OWNER TO geokrety;

--
-- TOC entry 3375 (class 0 OID 0)
-- Dependencies: 225
-- Name: news_comments_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.news_comments_id_seq OWNED BY geokrety.gk_news_comments.id;


--
-- TOC entry 223 (class 1259 OID 21056)
-- Name: news_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.news_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.news_id_seq OWNER TO geokrety;

--
-- TOC entry 3376 (class 0 OID 0)
-- Dependencies: 223
-- Name: news_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.news_id_seq OWNED BY geokrety.gk_news.id;


--
-- TOC entry 229 (class 1259 OID 21083)
-- Name: owner_codes_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.owner_codes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.owner_codes_id_seq OWNER TO geokrety;

--
-- TOC entry 3377 (class 0 OID 0)
-- Dependencies: 229
-- Name: owner_codes_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.owner_codes_id_seq OWNED BY geokrety.gk_owner_codes.id;


--
-- TOC entry 231 (class 1259 OID 21090)
-- Name: password_tokens_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.password_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.password_tokens_id_seq OWNER TO geokrety;

--
-- TOC entry 3378 (class 0 OID 0)
-- Dependencies: 231
-- Name: password_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.password_tokens_id_seq OWNED BY geokrety.gk_password_tokens.id;


--
-- TOC entry 248 (class 1259 OID 21180)
-- Name: phinxlog; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.phinxlog (
    version numeric NOT NULL,
    migration_name character varying(100),
    start_time timestamp with time zone,
    end_time timestamp with time zone,
    breakpoint boolean DEFAULT false NOT NULL
);


ALTER TABLE geokrety.phinxlog OWNER TO geokrety;

--
-- TOC entry 233 (class 1259 OID 21098)
-- Name: pictures_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.pictures_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.pictures_id_seq OWNER TO geokrety;

--
-- TOC entry 3379 (class 0 OID 0)
-- Dependencies: 233
-- Name: pictures_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.pictures_id_seq OWNED BY geokrety.gk_pictures.id;


--
-- TOC entry 235 (class 1259 OID 21112)
-- Name: races_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.races_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.races_id_seq OWNER TO geokrety;

--
-- TOC entry 3380 (class 0 OID 0)
-- Dependencies: 235
-- Name: races_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.races_id_seq OWNED BY geokrety.gk_races.id;


--
-- TOC entry 237 (class 1259 OID 21123)
-- Name: races_participants_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.races_participants_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.races_participants_id_seq OWNER TO geokrety;

--
-- TOC entry 3381 (class 0 OID 0)
-- Dependencies: 237
-- Name: races_participants_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.races_participants_id_seq OWNED BY geokrety.gk_races_participants.id;


--
-- TOC entry 250 (class 1259 OID 21189)
-- Name: scripts; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.scripts (
    id bigint NOT NULL,
    name character varying(128) NOT NULL,
    last_run_datetime timestamp with time zone
);


ALTER TABLE geokrety.scripts OWNER TO geokrety;

--
-- TOC entry 249 (class 1259 OID 21187)
-- Name: scripts_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.scripts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.scripts_id_seq OWNER TO geokrety;

--
-- TOC entry 3382 (class 0 OID 0)
-- Dependencies: 249
-- Name: scripts_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.scripts_id_seq OWNED BY geokrety.scripts.id;


--
-- TOC entry 251 (class 1259 OID 21193)
-- Name: sessions; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.sessions (
    session_id character varying(255) NOT NULL,
    data text,
    ip character varying(45),
    agent character varying(300),
    stamp bigint
);


ALTER TABLE geokrety.sessions OWNER TO geokrety;

--
-- TOC entry 239 (class 1259 OID 21133)
-- Name: users_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.users_id_seq OWNER TO geokrety;

--
-- TOC entry 3383 (class 0 OID 0)
-- Dependencies: 239
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.users_id_seq OWNED BY geokrety.gk_users.id;


--
-- TOC entry 241 (class 1259 OID 21151)
-- Name: watched_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.watched_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.watched_id_seq OWNER TO geokrety;

--
-- TOC entry 3384 (class 0 OID 0)
-- Dependencies: 241
-- Name: watched_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.watched_id_seq OWNED BY geokrety.gk_watched.id;


--
-- TOC entry 243 (class 1259 OID 21158)
-- Name: waypoints_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: geokrety
--

CREATE SEQUENCE geokrety.waypoints_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE geokrety.waypoints_id_seq OWNER TO geokrety;

--
-- TOC entry 3385 (class 0 OID 0)
-- Dependencies: 243
-- Name: waypoints_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.waypoints_id_seq OWNED BY geokrety.gk_waypoints.id;


--
-- TOC entry 2951 (class 2604 OID 20972)
-- Name: gk_account_activation id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_account_activation ALTER COLUMN id SET DEFAULT nextval('geokrety.account_activation_id_seq'::regclass);


--
-- TOC entry 2955 (class 2604 OID 20987)
-- Name: gk_badges id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_badges ALTER COLUMN id SET DEFAULT nextval('geokrety.badges_id_seq'::regclass);


--
-- TOC entry 2958 (class 2604 OID 20994)
-- Name: gk_email_activation id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_email_activation ALTER COLUMN id SET DEFAULT nextval('geokrety.email_activation_id_seq'::regclass);


--
-- TOC entry 2963 (class 2604 OID 21005)
-- Name: gk_geokrety id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety ALTER COLUMN id SET DEFAULT nextval('geokrety.geokrety_id_seq'::regclass);


--
-- TOC entry 2973 (class 2604 OID 21019)
-- Name: gk_geokrety_rating id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety_rating ALTER COLUMN id SET DEFAULT nextval('geokrety.geokrety_rating_id_seq'::regclass);


--
-- TOC entry 2975 (class 2604 OID 21027)
-- Name: gk_mails id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_mails ALTER COLUMN id SET DEFAULT nextval('geokrety.mails_id_seq'::regclass);


--
-- TOC entry 2984 (class 2604 OID 21047)
-- Name: gk_moves id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_moves ALTER COLUMN id SET DEFAULT nextval('geokrety.moves_id_seq'::regclass);


--
-- TOC entry 2978 (class 2604 OID 21037)
-- Name: gk_moves_comments id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_moves_comments ALTER COLUMN id SET DEFAULT nextval('geokrety.move_comments_id_seq'::regclass);


--
-- TOC entry 2988 (class 2604 OID 21061)
-- Name: gk_news id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news ALTER COLUMN id SET DEFAULT nextval('geokrety.news_id_seq'::regclass);


--
-- TOC entry 2992 (class 2604 OID 21072)
-- Name: gk_news_comments id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news_comments ALTER COLUMN id SET DEFAULT nextval('geokrety.news_comments_id_seq'::regclass);


--
-- TOC entry 2994 (class 2604 OID 21082)
-- Name: gk_news_comments_access id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news_comments_access ALTER COLUMN id SET DEFAULT nextval('geokrety.news_comments_access_id_seq'::regclass);


--
-- TOC entry 2995 (class 2604 OID 21088)
-- Name: gk_owner_codes id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_owner_codes ALTER COLUMN id SET DEFAULT nextval('geokrety.owner_codes_id_seq'::regclass);


--
-- TOC entry 2997 (class 2604 OID 21095)
-- Name: gk_password_tokens id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_password_tokens ALTER COLUMN id SET DEFAULT nextval('geokrety.password_tokens_id_seq'::regclass);


--
-- TOC entry 3001 (class 2604 OID 21103)
-- Name: gk_pictures id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_pictures ALTER COLUMN id SET DEFAULT nextval('geokrety.pictures_id_seq'::regclass);


--
-- TOC entry 3005 (class 2604 OID 21117)
-- Name: gk_races id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_races ALTER COLUMN id SET DEFAULT nextval('geokrety.races_id_seq'::regclass);


--
-- TOC entry 3014 (class 2604 OID 21128)
-- Name: gk_races_participants id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_races_participants ALTER COLUMN id SET DEFAULT nextval('geokrety.races_participants_id_seq'::regclass);


--
-- TOC entry 3038 (class 2604 OID 156210)
-- Name: gk_statistics_counters id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_statistics_counters ALTER COLUMN id SET DEFAULT nextval('geokrety.gk_statistics_counters_id_seq'::regclass);


--
-- TOC entry 3037 (class 2604 OID 155225)
-- Name: gk_statistics_daily_counters id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_statistics_daily_counters ALTER COLUMN id SET DEFAULT nextval('geokrety.gk_statistics_daily_counters_id_seq'::regclass);


--
-- TOC entry 3020 (class 2604 OID 21138)
-- Name: gk_users id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_users ALTER COLUMN id SET DEFAULT nextval('geokrety.users_id_seq'::regclass);


--
-- TOC entry 3028 (class 2604 OID 21156)
-- Name: gk_watched id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_watched ALTER COLUMN id SET DEFAULT nextval('geokrety.watched_id_seq'::regclass);


--
-- TOC entry 3032 (class 2604 OID 21163)
-- Name: gk_waypoints id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_waypoints ALTER COLUMN id SET DEFAULT nextval('geokrety.waypoints_id_seq'::regclass);


--
-- TOC entry 3036 (class 2604 OID 21192)
-- Name: scripts id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.scripts ALTER COLUMN id SET DEFAULT nextval('geokrety.scripts_id_seq'::regclass);


--
-- TOC entry 3105 (class 2606 OID 182256)
-- Name: gk_pictures gk_pictures_id; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_pictures
    ADD CONSTRAINT gk_pictures_id PRIMARY KEY (id);


--
-- TOC entry 3146 (class 2606 OID 156384)
-- Name: gk_statistics_counters gk_statistics_counters_id; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_statistics_counters
    ADD CONSTRAINT gk_statistics_counters_id PRIMARY KEY (id);


--
-- TOC entry 3142 (class 2606 OID 155228)
-- Name: gk_statistics_daily_counters gk_statistics_daily_counters_date; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_statistics_daily_counters
    ADD CONSTRAINT gk_statistics_daily_counters_date UNIQUE (date);


--
-- TOC entry 3144 (class 2606 OID 156382)
-- Name: gk_statistics_daily_counters gk_statistics_daily_counters_id; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_statistics_daily_counters
    ADD CONSTRAINT gk_statistics_daily_counters_id PRIMARY KEY (id);


--
-- TOC entry 3148 (class 2606 OID 157050)
-- Name: gk_waypoints_gc gk_waypoints_gc_id; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_waypoints_gc
    ADD CONSTRAINT gk_waypoints_gc_id PRIMARY KEY (id);


--
-- TOC entry 3150 (class 2606 OID 156378)
-- Name: gk_waypoints_gc gk_waypoints_gc_waypoint; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_waypoints_gc
    ADD CONSTRAINT gk_waypoints_gc_waypoint UNIQUE (waypoint);


--
-- TOC entry 3131 (class 2606 OID 92337)
-- Name: gk_waypoints_sync gk_waypoints_sync_service_id; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_waypoints_sync
    ADD CONSTRAINT gk_waypoints_sync_service_id UNIQUE (service_id);


--
-- TOC entry 3133 (class 2606 OID 92625)
-- Name: gk_waypoints_types gk_waypoints_types_type; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_waypoints_types
    ADD CONSTRAINT gk_waypoints_types_type UNIQUE (type);


--
-- TOC entry 3041 (class 2606 OID 44285)
-- Name: gk_account_activation idx_20969_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_account_activation
    ADD CONSTRAINT idx_20969_primary PRIMARY KEY (id);


--
-- TOC entry 3044 (class 2606 OID 44287)
-- Name: gk_badges idx_20984_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_badges
    ADD CONSTRAINT idx_20984_primary PRIMARY KEY (id);


--
-- TOC entry 3048 (class 2606 OID 44288)
-- Name: gk_email_activation idx_20991_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_email_activation
    ADD CONSTRAINT idx_20991_primary PRIMARY KEY (id);


--
-- TOC entry 3059 (class 2606 OID 44294)
-- Name: gk_geokrety idx_21002_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT idx_21002_primary PRIMARY KEY (id);


--
-- TOC entry 3062 (class 2606 OID 44289)
-- Name: gk_geokrety_rating idx_21016_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety_rating
    ADD CONSTRAINT idx_21016_primary PRIMARY KEY (id);


--
-- TOC entry 3069 (class 2606 OID 44290)
-- Name: gk_moves_comments idx_21034_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_moves_comments
    ADD CONSTRAINT idx_21034_primary PRIMARY KEY (id);


--
-- TOC entry 3079 (class 2606 OID 44301)
-- Name: gk_moves idx_21044_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_moves
    ADD CONSTRAINT idx_21044_primary PRIMARY KEY (id);


--
-- TOC entry 3085 (class 2606 OID 44291)
-- Name: gk_news idx_21058_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news
    ADD CONSTRAINT idx_21058_primary PRIMARY KEY (id);


--
-- TOC entry 3090 (class 2606 OID 44298)
-- Name: gk_news_comments idx_21069_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news_comments
    ADD CONSTRAINT idx_21069_primary PRIMARY KEY (id);


--
-- TOC entry 3093 (class 2606 OID 99254)
-- Name: gk_news_comments_access idx_21079_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news_comments_access
    ADD CONSTRAINT idx_21079_primary PRIMARY KEY (news, author);


--
-- TOC entry 3098 (class 2606 OID 44299)
-- Name: gk_owner_codes idx_21085_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_owner_codes
    ADD CONSTRAINT idx_21085_primary PRIMARY KEY (id);


--
-- TOC entry 3102 (class 2606 OID 44293)
-- Name: gk_password_tokens idx_21092_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_password_tokens
    ADD CONSTRAINT idx_21092_primary PRIMARY KEY (id);


--
-- TOC entry 3109 (class 2606 OID 44300)
-- Name: gk_races idx_21114_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_races
    ADD CONSTRAINT idx_21114_primary PRIMARY KEY (id);


--
-- TOC entry 3117 (class 2606 OID 44295)
-- Name: gk_users idx_21135_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_users
    ADD CONSTRAINT idx_21135_primary PRIMARY KEY (id);


--
-- TOC entry 3124 (class 2606 OID 44296)
-- Name: gk_watched idx_21153_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_watched
    ADD CONSTRAINT idx_21153_primary PRIMARY KEY (id);


--
-- TOC entry 3128 (class 2606 OID 44304)
-- Name: gk_waypoints idx_21160_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_waypoints
    ADD CONSTRAINT idx_21160_primary PRIMARY KEY (id);


--
-- TOC entry 3135 (class 2606 OID 44302)
-- Name: phinxlog idx_21180_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.phinxlog
    ADD CONSTRAINT idx_21180_primary PRIMARY KEY (version);


--
-- TOC entry 3138 (class 2606 OID 44297)
-- Name: scripts idx_21189_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.scripts
    ADD CONSTRAINT idx_21189_primary PRIMARY KEY (id);


--
-- TOC entry 3140 (class 2606 OID 44303)
-- Name: sessions idx_21193_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.sessions
    ADD CONSTRAINT idx_21193_primary PRIMARY KEY (session_id);


--
-- TOC entry 3106 (class 1259 OID 191322)
-- Name: gk_pictures_key; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX gk_pictures_key ON geokrety.gk_pictures USING btree (key);


--
-- TOC entry 3126 (class 1259 OID 93776)
-- Name: gk_waypoints_waypoint; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX gk_waypoints_waypoint ON geokrety.gk_waypoints USING btree (waypoint);


--
-- TOC entry 3042 (class 1259 OID 21199)
-- Name: idx_20969_user; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_20969_user ON geokrety.gk_account_activation USING btree ("user");


--
-- TOC entry 3045 (class 1259 OID 91573)
-- Name: idx_20984_timestamp; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_20984_timestamp ON geokrety.gk_badges USING btree (awarded_on_datetime);


--
-- TOC entry 3046 (class 1259 OID 21203)
-- Name: idx_20984_userid; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_20984_userid ON geokrety.gk_badges USING btree (holder);


--
-- TOC entry 3049 (class 1259 OID 21209)
-- Name: idx_20991_token; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_20991_token ON geokrety.gk_email_activation USING btree (token);


--
-- TOC entry 3050 (class 1259 OID 21211)
-- Name: idx_20991_user; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_20991_user ON geokrety.gk_email_activation USING btree ("user");


--
-- TOC entry 3051 (class 1259 OID 21304)
-- Name: idx_21002_avatarid; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21002_avatarid ON geokrety.gk_geokrety USING btree (avatar);


--
-- TOC entry 3052 (class 1259 OID 21305)
-- Name: idx_21002_hands_of_index; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21002_hands_of_index ON geokrety.gk_geokrety USING btree (holder);


--
-- TOC entry 3053 (class 1259 OID 21306)
-- Name: idx_21002_id; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE UNIQUE INDEX idx_21002_id ON geokrety.gk_geokrety USING btree (tracking_code);


--
-- TOC entry 3054 (class 1259 OID 21310)
-- Name: idx_21002_nr; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21002_nr ON geokrety.gk_geokrety USING btree (tracking_code);


--
-- TOC entry 3055 (class 1259 OID 21307)
-- Name: idx_21002_ost_log_id; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21002_ost_log_id ON geokrety.gk_geokrety USING btree (last_log);


--
-- TOC entry 3056 (class 1259 OID 21309)
-- Name: idx_21002_ost_pozycja_id; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21002_ost_pozycja_id ON geokrety.gk_geokrety USING btree (last_position);


--
-- TOC entry 3057 (class 1259 OID 21311)
-- Name: idx_21002_owner; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21002_owner ON geokrety.gk_geokrety USING btree (owner);


--
-- TOC entry 3060 (class 1259 OID 91743)
-- Name: idx_21016_geokret; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21016_geokret ON geokrety.gk_geokrety_rating USING btree (geokret);


--
-- TOC entry 3063 (class 1259 OID 100818)
-- Name: idx_21016_user; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21016_user ON geokrety.gk_geokrety_rating USING btree (author);


--
-- TOC entry 3064 (class 1259 OID 100888)
-- Name: idx_21024_from; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21024_from ON geokrety.gk_mails USING btree (from_user);


--
-- TOC entry 3065 (class 1259 OID 21315)
-- Name: idx_21024_id_maila; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE UNIQUE INDEX idx_21024_id_maila ON geokrety.gk_mails USING btree (id);


--
-- TOC entry 3066 (class 1259 OID 100889)
-- Name: idx_21024_to; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21024_to ON geokrety.gk_mails USING btree (to_user);


--
-- TOC entry 3067 (class 1259 OID 21222)
-- Name: idx_21034_kret_id; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21034_kret_id ON geokrety.gk_moves_comments USING btree (geokret);


--
-- TOC entry 3070 (class 1259 OID 21223)
-- Name: idx_21034_ruch_id; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21034_ruch_id ON geokrety.gk_moves_comments USING btree (move);


--
-- TOC entry 3071 (class 1259 OID 91821)
-- Name: idx_21034_user_id; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21034_user_id ON geokrety.gk_moves_comments USING btree (author);


--
-- TOC entry 3072 (class 1259 OID 91852)
-- Name: idx_21044_alt; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21044_alt ON geokrety.gk_moves USING btree (alt);


--
-- TOC entry 3073 (class 1259 OID 91604)
-- Name: idx_21044_data; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21044_data ON geokrety.gk_moves USING btree (created_on_datetime);


--
-- TOC entry 3074 (class 1259 OID 91605)
-- Name: idx_21044_data_dodania; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21044_data_dodania ON geokrety.gk_moves USING btree (moved_on_datetime);


--
-- TOC entry 3075 (class 1259 OID 44267)
-- Name: idx_21044_id_2; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21044_id_2 ON geokrety.gk_moves USING btree (geokret);


--
-- TOC entry 3076 (class 1259 OID 44275)
-- Name: idx_21044_lat; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21044_lat ON geokrety.gk_moves USING btree (lat);


--
-- TOC entry 3077 (class 1259 OID 44268)
-- Name: idx_21044_lon; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21044_lon ON geokrety.gk_moves USING btree (lon);


--
-- TOC entry 3080 (class 1259 OID 91606)
-- Name: idx_21044_timestamp; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21044_timestamp ON geokrety.gk_moves USING btree (updated_on_datetime);


--
-- TOC entry 3081 (class 1259 OID 91844)
-- Name: idx_21044_user; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21044_user ON geokrety.gk_moves USING btree (author);


--
-- TOC entry 3082 (class 1259 OID 151787)
-- Name: idx_21044_waypoint; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21044_waypoint ON geokrety.gk_moves USING btree (waypoint);


--
-- TOC entry 3083 (class 1259 OID 91611)
-- Name: idx_21058_date; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21058_date ON geokrety.gk_news USING btree (created_on_datetime);


--
-- TOC entry 3086 (class 1259 OID 21230)
-- Name: idx_21058_userid; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21058_userid ON geokrety.gk_news USING btree (author);


--
-- TOC entry 3087 (class 1259 OID 41072)
-- Name: idx_21069_author; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21069_author ON geokrety.gk_news_comments USING btree (author);


--
-- TOC entry 3088 (class 1259 OID 41071)
-- Name: idx_21069_news; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21069_news ON geokrety.gk_news_comments USING btree (news);


--
-- TOC entry 3091 (class 1259 OID 21234)
-- Name: idx_21079_id; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE UNIQUE INDEX idx_21079_id ON geokrety.gk_news_comments_access USING btree (id);


--
-- TOC entry 3094 (class 1259 OID 99255)
-- Name: idx_21079_user; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21079_user ON geokrety.gk_news_comments_access USING btree (author);


--
-- TOC entry 3095 (class 1259 OID 41069)
-- Name: idx_21085_code; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21085_code ON geokrety.gk_owner_codes USING btree (token);


--
-- TOC entry 3096 (class 1259 OID 41073)
-- Name: idx_21085_kret_id; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21085_kret_id ON geokrety.gk_owner_codes USING btree (geokret);


--
-- TOC entry 3099 (class 1259 OID 41068)
-- Name: idx_21085_user; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21085_user ON geokrety.gk_owner_codes USING btree ("user");


--
-- TOC entry 3100 (class 1259 OID 91624)
-- Name: idx_21092_created_on_datetime; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21092_created_on_datetime ON geokrety.gk_password_tokens USING btree (created_on_datetime);


--
-- TOC entry 3103 (class 1259 OID 21237)
-- Name: idx_21092_user; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21092_user ON geokrety.gk_password_tokens USING btree ("user");


--
-- TOC entry 3107 (class 1259 OID 91971)
-- Name: idx_21114_organizer; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21114_organizer ON geokrety.gk_races USING btree (organizer);


--
-- TOC entry 3110 (class 1259 OID 21258)
-- Name: idx_21125_geokret; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21125_geokret ON geokrety.gk_races_participants USING btree (geokret);


--
-- TOC entry 3111 (class 1259 OID 21260)
-- Name: idx_21125_race; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21125_race ON geokrety.gk_races_participants USING btree (race);


--
-- TOC entry 3112 (class 1259 OID 21259)
-- Name: idx_21125_racegkid; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE UNIQUE INDEX idx_21125_racegkid ON geokrety.gk_races_participants USING btree (id);


--
-- TOC entry 3113 (class 1259 OID 21320)
-- Name: idx_21135_avatar; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21135_avatar ON geokrety.gk_users USING btree (avatar);


--
-- TOC entry 3114 (class 1259 OID 21322)
-- Name: idx_21135_email; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21135_email ON geokrety.gk_users USING btree (email);


--
-- TOC entry 3115 (class 1259 OID 92110)
-- Name: idx_21135_ostatni_login; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21135_ostatni_login ON geokrety.gk_users USING btree (last_login_datetime);


--
-- TOC entry 3118 (class 1259 OID 21321)
-- Name: idx_21135_secid; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21135_secid ON geokrety.gk_users USING btree (secid);


--
-- TOC entry 3119 (class 1259 OID 21319)
-- Name: idx_21135_user; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE UNIQUE INDEX idx_21135_user ON geokrety.gk_users USING btree (username);


--
-- TOC entry 3120 (class 1259 OID 21323)
-- Name: idx_21135_username; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21135_username ON geokrety.gk_users USING btree (username);


--
-- TOC entry 3121 (class 1259 OID 21324)
-- Name: idx_21135_username_email; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21135_username_email ON geokrety.gk_users USING btree (username, email);


--
-- TOC entry 3122 (class 1259 OID 21328)
-- Name: idx_21153_id; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21153_id ON geokrety.gk_watched USING btree (geokret);


--
-- TOC entry 3125 (class 1259 OID 21330)
-- Name: idx_21153_userid; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21153_userid ON geokrety.gk_watched USING btree ("user");


--
-- TOC entry 3129 (class 1259 OID 92192)
-- Name: idx_21171_unique_kraj; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE UNIQUE INDEX idx_21171_unique_kraj ON geokrety.gk_waypoints_country USING btree (original);


--
-- TOC entry 3136 (class 1259 OID 21333)
-- Name: idx_21189_name; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE UNIQUE INDEX idx_21189_name ON geokrety.scripts USING btree (name);


--
-- TOC entry 3186 (class 2620 OID 91576)
-- Name: gk_badges updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_badges FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- TOC entry 3187 (class 2620 OID 91584)
-- Name: gk_email_activation updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_email_activation FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- TOC entry 3188 (class 2620 OID 91589)
-- Name: gk_geokrety updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_geokrety FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- TOC entry 3189 (class 2620 OID 91594)
-- Name: gk_geokrety_rating updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_geokrety_rating FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- TOC entry 3191 (class 2620 OID 91609)
-- Name: gk_moves updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- TOC entry 3190 (class 2620 OID 91601)
-- Name: gk_moves_comments updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_moves_comments FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- TOC entry 3192 (class 2620 OID 181994)
-- Name: gk_news_comments updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_news_comments FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- TOC entry 3193 (class 2620 OID 91627)
-- Name: gk_password_tokens updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_password_tokens FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- TOC entry 3194 (class 2620 OID 91954)
-- Name: gk_pictures updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_pictures FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- TOC entry 3195 (class 2620 OID 91982)
-- Name: gk_races updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_races FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- TOC entry 3196 (class 2620 OID 92134)
-- Name: gk_races_participants updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_races_participants FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- TOC entry 3197 (class 2620 OID 92135)
-- Name: gk_users updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_users FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- TOC entry 3198 (class 2620 OID 92160)
-- Name: gk_watched updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_watched FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- TOC entry 3199 (class 2620 OID 92161)
-- Name: gk_waypoints updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_waypoints FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- TOC entry 3151 (class 2606 OID 217874)
-- Name: gk_account_activation gk_account_activation_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_account_activation
    ADD CONSTRAINT gk_account_activation_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- TOC entry 3152 (class 2606 OID 217879)
-- Name: gk_badges gk_badges_holder_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_badges
    ADD CONSTRAINT gk_badges_holder_fkey FOREIGN KEY (holder) REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- TOC entry 3153 (class 2606 OID 91682)
-- Name: gk_email_activation gk_email_activation_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_email_activation
    ADD CONSTRAINT gk_email_activation_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- TOC entry 3158 (class 2606 OID 217904)
-- Name: gk_geokrety gk_geokrety_avatar_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT gk_geokrety_avatar_fkey FOREIGN KEY (avatar) REFERENCES geokrety.gk_pictures(id) ON DELETE SET NULL;


--
-- TOC entry 3157 (class 2606 OID 217899)
-- Name: gk_geokrety gk_geokrety_holder_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT gk_geokrety_holder_fkey FOREIGN KEY (holder) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- TOC entry 3156 (class 2606 OID 217894)
-- Name: gk_geokrety gk_geokrety_last_log_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT gk_geokrety_last_log_fkey FOREIGN KEY (last_log) REFERENCES geokrety.gk_moves(id) ON DELETE SET NULL;


--
-- TOC entry 3155 (class 2606 OID 217889)
-- Name: gk_geokrety gk_geokrety_last_position_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT gk_geokrety_last_position_fkey FOREIGN KEY (last_position) REFERENCES geokrety.gk_moves(id) ON DELETE SET NULL;


--
-- TOC entry 3154 (class 2606 OID 217884)
-- Name: gk_geokrety gk_geokrety_owner_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT gk_geokrety_owner_fkey FOREIGN KEY (owner) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- TOC entry 3160 (class 2606 OID 217914)
-- Name: gk_geokrety_rating gk_geokrety_rating_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety_rating
    ADD CONSTRAINT gk_geokrety_rating_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- TOC entry 3159 (class 2606 OID 217909)
-- Name: gk_geokrety_rating gk_geokrety_rating_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety_rating
    ADD CONSTRAINT gk_geokrety_rating_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE CASCADE;


--
-- TOC entry 3161 (class 2606 OID 217919)
-- Name: gk_mails gk_mails_from_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_mails
    ADD CONSTRAINT gk_mails_from_user_fkey FOREIGN KEY (from_user) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- TOC entry 3162 (class 2606 OID 217924)
-- Name: gk_mails gk_mails_to_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_mails
    ADD CONSTRAINT gk_mails_to_user_fkey FOREIGN KEY (to_user) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- TOC entry 3166 (class 2606 OID 217944)
-- Name: gk_moves gk_moves_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_moves
    ADD CONSTRAINT gk_moves_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- TOC entry 3165 (class 2606 OID 217939)
-- Name: gk_moves_comments gk_moves_comments_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_moves_comments
    ADD CONSTRAINT gk_moves_comments_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- TOC entry 3164 (class 2606 OID 217934)
-- Name: gk_moves_comments gk_moves_comments_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_moves_comments
    ADD CONSTRAINT gk_moves_comments_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE CASCADE;


--
-- TOC entry 3163 (class 2606 OID 217929)
-- Name: gk_moves_comments gk_moves_comments_move_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_moves_comments
    ADD CONSTRAINT gk_moves_comments_move_fkey FOREIGN KEY (move) REFERENCES geokrety.gk_moves(id) ON DELETE CASCADE;


--
-- TOC entry 3167 (class 2606 OID 217949)
-- Name: gk_moves gk_moves_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_moves
    ADD CONSTRAINT gk_moves_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE CASCADE;


--
-- TOC entry 3168 (class 2606 OID 217954)
-- Name: gk_news gk_news_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news
    ADD CONSTRAINT gk_news_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- TOC entry 3172 (class 2606 OID 217974)
-- Name: gk_news_comments_access gk_news_comments_access_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news_comments_access
    ADD CONSTRAINT gk_news_comments_access_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- TOC entry 3171 (class 2606 OID 217969)
-- Name: gk_news_comments_access gk_news_comments_access_news_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news_comments_access
    ADD CONSTRAINT gk_news_comments_access_news_fkey FOREIGN KEY (news) REFERENCES geokrety.gk_news(id) ON DELETE CASCADE;


--
-- TOC entry 3170 (class 2606 OID 217964)
-- Name: gk_news_comments gk_news_comments_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news_comments
    ADD CONSTRAINT gk_news_comments_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- TOC entry 3169 (class 2606 OID 217959)
-- Name: gk_news_comments gk_news_comments_news_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news_comments
    ADD CONSTRAINT gk_news_comments_news_fkey FOREIGN KEY (news) REFERENCES geokrety.gk_news(id) ON DELETE CASCADE;


--
-- TOC entry 3173 (class 2606 OID 217979)
-- Name: gk_owner_codes gk_owner_codes_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_owner_codes
    ADD CONSTRAINT gk_owner_codes_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE CASCADE;


--
-- TOC entry 3174 (class 2606 OID 217984)
-- Name: gk_owner_codes gk_owner_codes_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_owner_codes
    ADD CONSTRAINT gk_owner_codes_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- TOC entry 3175 (class 2606 OID 217989)
-- Name: gk_password_tokens gk_password_tokens_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_password_tokens
    ADD CONSTRAINT gk_password_tokens_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- TOC entry 3179 (class 2606 OID 218009)
-- Name: gk_pictures gk_pictures_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_pictures
    ADD CONSTRAINT gk_pictures_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- TOC entry 3177 (class 2606 OID 217999)
-- Name: gk_pictures gk_pictures_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_pictures
    ADD CONSTRAINT gk_pictures_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE SET NULL;


--
-- TOC entry 3176 (class 2606 OID 217994)
-- Name: gk_pictures gk_pictures_move_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_pictures
    ADD CONSTRAINT gk_pictures_move_fkey FOREIGN KEY (move) REFERENCES geokrety.gk_moves(id) ON DELETE SET NULL;


--
-- TOC entry 3178 (class 2606 OID 218004)
-- Name: gk_pictures gk_pictures_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_pictures
    ADD CONSTRAINT gk_pictures_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- TOC entry 3180 (class 2606 OID 218014)
-- Name: gk_races gk_races_organizer_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_races
    ADD CONSTRAINT gk_races_organizer_fkey FOREIGN KEY (organizer) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- TOC entry 3182 (class 2606 OID 218024)
-- Name: gk_races_participants gk_races_participants_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_races_participants
    ADD CONSTRAINT gk_races_participants_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE CASCADE;


--
-- TOC entry 3181 (class 2606 OID 218019)
-- Name: gk_races_participants gk_races_participants_race_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_races_participants
    ADD CONSTRAINT gk_races_participants_race_fkey FOREIGN KEY (race) REFERENCES geokrety.gk_races(id) ON DELETE CASCADE;


--
-- TOC entry 3183 (class 2606 OID 218029)
-- Name: gk_users gk_users_avatar_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_users
    ADD CONSTRAINT gk_users_avatar_fkey FOREIGN KEY (avatar) REFERENCES geokrety.gk_pictures(id) ON DELETE SET NULL;


--
-- TOC entry 3185 (class 2606 OID 218039)
-- Name: gk_watched gk_watched_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_watched
    ADD CONSTRAINT gk_watched_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE CASCADE;


--
-- TOC entry 3184 (class 2606 OID 218034)
-- Name: gk_watched gk_watched_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_watched
    ADD CONSTRAINT gk_watched_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


-- Completed on 2020-03-31 19:52:58 GMT

--
-- PostgreSQL database dump complete
--

