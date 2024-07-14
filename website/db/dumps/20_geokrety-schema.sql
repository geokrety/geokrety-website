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
-- Name: geokrety; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA IF NOT EXISTS geokrety;


--
-- Name: action_type; Type: TYPE; Schema: geokrety; Owner: -
--

CREATE TYPE geokrety.action_type AS ENUM (
    'manual',
    'automatic'
);


--
-- Name: authentication_method; Type: TYPE; Schema: geokrety; Owner: -
--

CREATE TYPE geokrety.authentication_method AS ENUM (
    'password',
    'secid',
    'devel',
    'oauth',
    'registration.activate',
    'registration.oauth',
    'api2secid',
    'google'
);


--
-- Name: account_activation_check_only_one_active_per_user(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.account_activation_check_only_one_active_per_user() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

IF NEW.used = 0::smallint THEN
    UPDATE "gk_account_activation"
    SET used = 3, -- TOKEN_DISABLED
    validating_ip = NULL,
    used_on_datetime = NULL
    WHERE "user" = NEW.user
    AND used = 0
    AND id != NEW.id;
END IF;

IF NEW.used = ANY ('{0,2,3}'::smallint[]) THEN
    NEW.used_on_datetime = NULL;
    NEW.validating_ip = NULL;
END IF;

RETURN NEW;
END;
$$;


--
-- Name: account_activation_check_validating_ip(inet, smallint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.account_activation_check_validating_ip(validating_ip inet, used smallint) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
BEGIN

IF used = ANY ('{0,2}'::smallint[]) AND validating_ip IS NULL THEN
	RETURN TRUE;
ELSIF used = ANY ('{1}'::smallint[]) AND validating_ip IS NOT NULL THEN
	RETURN TRUE;
ELSIF used = ANY ('{3}'::smallint[]) THEN
	RETURN TRUE;
END IF;

RETURN FALSE;
END;
$$;


--
-- Name: account_activation_disable_all(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.account_activation_disable_all() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

UPDATE "gk_account_activation"
SET used = 3 -- TOKEN_DISABLED
WHERE "user" = NEW.id
AND used = 0;

RETURN NEW;
END;
$$;


--
-- Name: account_activation_token_generate(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.account_activation_token_generate() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

IF (NEW.token IS NOT NULL) THEN
	RETURN NEW;
END IF;

NEW.token = generate_verification_token();

RETURN NEW;
END;$$;


--
-- Name: coords2position(double precision, double precision, integer); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.coords2position(lat double precision, lon double precision, OUT "position" public.geography, srid integer DEFAULT 4326) RETURNS public.geography
    LANGUAGE sql
    AS $$SELECT public.ST_SetSRID(public.ST_MakePoint(lon, lat), srid)::public.geography as position;$$;


--
-- Name: delete_user(bigint, boolean); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.delete_user(user_id bigint, clear_comments boolean DEFAULT false) RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN

IF clear_comments IS TRUE THEN
    UPDATE gk_moves
    SET comment='Comment suppressed'
    WHERE author = user_id;

    UPDATE gk_moves_comments
    SET content='Comment suppressed'
    WHERE author = user_id;
END IF;

DELETE FROM gk_users
WHERE id = user_id;

END;
$$;


--
-- Name: email_activation_check_email_already_used(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.email_activation_check_email_already_used() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

IF COUNT(*) > 0 FROM "gk_users" WHERE "id" = NEW.user AND _email_hash = NEW._email_hash THEN
       RAISE 'Email address already used';
END IF;

RETURN NEW;
END;$$;


--
-- Name: email_activation_check_only_one_active_per_user(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.email_activation_check_only_one_active_per_user() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

UPDATE "gk_email_activation"
SET used = 4 -- TOKEN_DISABLED
WHERE "user" = NEW.user AND used = 0;

RETURN NEW;
END;$$;


--
-- Name: email_activation_check_used_ip_datetime(smallint, inet, timestamp with time zone, inet, timestamp with time zone); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.email_activation_check_used_ip_datetime(used smallint, updating_ip inet, used_on_datetime timestamp with time zone, reverting_ip inet, reverted_on_datetime timestamp with time zone) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN

IF "used" = 0 AND updating_ip IS NULL AND used_on_datetime IS NULL AND reverting_ip IS NULL AND reverted_on_datetime IS NULL THEN
	RETURN TRUE;
ELSIF "used" = 1 AND updating_ip IS NOT NULL AND used_on_datetime IS NOT NULL AND reverting_ip IS NULL AND reverted_on_datetime IS NULL THEN
	RETURN TRUE;
ELSIF "used" = 2 AND updating_ip IS NOT NULL AND used_on_datetime IS NOT NULL AND reverting_ip IS NOT NULL AND reverted_on_datetime IS NOT NULL THEN
	RETURN TRUE;
ELSIF "used" = 2 AND updating_ip IS NULL AND used_on_datetime IS NULL AND reverting_ip IS NOT NULL AND reverted_on_datetime IS NOT NULL THEN
	RETURN TRUE;
ELSIF "used" = 3 AND updating_ip IS NULL AND used_on_datetime IS NULL AND reverting_ip IS NULL AND reverted_on_datetime IS NULL THEN
	RETURN TRUE;
ELSIF "used" = 4 AND updating_ip IS NULL AND used_on_datetime IS NULL AND reverting_ip IS NULL AND reverted_on_datetime IS NULL THEN
	RETURN TRUE;
ELSIF "used" = 5 AND updating_ip IS NOT NULL AND used_on_datetime IS NOT NULL AND reverting_ip IS NOT NULL AND reverted_on_datetime IS NOT NULL THEN
	RETURN TRUE;
ELSIF "used" = 6 AND updating_ip IS NOT NULL AND used_on_datetime IS NOT NULL AND reverting_ip IS NOT NULL AND reverted_on_datetime IS NOT NULL THEN
	RETURN TRUE;
END IF;

RETURN FALSE;
END;$$;


--
-- Name: email_activation_token_generate(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.email_activation_token_generate() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

IF (NEW.token IS NULL) THEN
	NEW.token = generate_verification_token();
END IF;

IF (NEW.revert_token IS NULL) THEN
	NEW.revert_token = generate_verification_token();
END IF;

RETURN NEW;
END;$$;


--
-- Name: email_revalidate_check_only_one_active_per_user(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.email_revalidate_check_only_one_active_per_user() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

UPDATE "gk_email_revalidate"
SET used = 3 -- TOKEN_DISABLED
WHERE "user" = NEW.user
AND used = 0
AND id != NEW.id;

RETURN NEW;
END;
$$;


--
-- Name: email_revalidate_validated_on_datetime_updater(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.email_revalidate_validated_on_datetime_updater() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

IF NEW.used = 0::smallint THEN
	NEW.validating_ip = NULL;
	NEW.validated_on_datetime = NULL;
	NEW.expired_on_datetime = NULL;
	NEW.disabled_on_datetime = NULL;
ELSIF NEW.used = 1::smallint AND NEW.validated_on_datetime IS NULL THEN
	NEW.validated_on_datetime = NOW();
	NEW.expired_on_datetime = NULL;
	NEW.disabled_on_datetime = NULL;
ELSIF NEW.used = 2::smallint THEN
	NEW.validating_ip = NULL;
	NEW.validated_on_datetime = NULL;
	NEW.expired_on_datetime = NOW();
	NEW.disabled_on_datetime = NULL;
ELSIF NEW.used = 3::smallint THEN
	NEW.validating_ip = NULL;
	NEW.validated_on_datetime = NULL;
	NEW.expired_on_datetime = NULL;
	NEW.disabled_on_datetime = NOW();
END IF;

RETURN NEW;
END;
$$;


--
-- Name: email_revalidate_validated_update_user(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.email_revalidate_validated_update_user() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

IF NEW.used = 1::smallint THEN
	UPDATE gk_users
	SET account_valid = 1
	WHERE id = NEW."user";
END IF;

RETURN NEW;
END;
$$;


--
-- Name: fresher_than(timestamp with time zone, integer, character varying); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.fresher_than(datetime timestamp with time zone, duration integer, unit character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN
	RETURN datetime > NOW() - CAST(duration || ' ' || unit as INTERVAL);
END;$$;


--
-- Name: generate_adoption_token(integer); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.generate_adoption_token(size integer DEFAULT 5) RETURNS character varying
    LANGUAGE sql
    AS $$SELECT array_to_string(array(select substr('0123456789',((random()*(10-1)+1)::integer),1) from generate_series(1,size)),'');$$;


--
-- Name: generate_password_token(integer); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.generate_password_token(size integer DEFAULT 42) RETURNS character varying
    LANGUAGE sql
    AS $$SELECT array_to_string(array(select substr('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz',((random()*(62-1)+1)::integer),1) from generate_series(1,size)),'');$$;


--
-- Name: generate_secid(integer); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.generate_secid(size integer DEFAULT 128) RETURNS character varying
    LANGUAGE sql
    AS $$SELECT array_to_string(array(select substr('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz',((random()*(62-1)+1)::integer),1) from generate_series(1,size)),'');$$;


--
-- Name: generate_tracking_code(integer); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.generate_tracking_code(size integer DEFAULT 6) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
DECLARE
    tracking_code character varying := '';
BEGIN

WHILE NOT(is_tracking_code_valid(tracking_code)) LOOP
    SELECT array_to_string(array(select substr('ABCDEFGHJKMNPQRSTUVWXYZ23456789',((random()*(31-1)+1)::integer),1) from generate_series(1,size)),'')
        INTO tracking_code;
END LOOP;

RETURN tracking_code;
END;
$$;


--
-- Name: generate_verification_token(integer); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.generate_verification_token(size integer DEFAULT 42) RETURNS character varying
    LANGUAGE sql
    AS $$SELECT array_to_string(array(select substr('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz',((random()*(62-1)+1)::integer),1) from generate_series(1,size)),'');$$;


--
-- Name: geokret_compute_missing(bigint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.geokret_compute_missing(lastposition_id bigint) RETURNS boolean
    LANGUAGE sql
    AS $$SELECT COUNT(*) > 0
FROM "gk_moves_comments"
WHERE "move" = lastposition_id
AND "type" = 1;
$$;


--
-- Name: geokret_compute_total_distance(bigint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.geokret_compute_total_distance(geokret_id bigint) RETURNS bigint
    LANGUAGE plpgsql
    AS $$DECLARE
total bigint;
BEGIN

SELECT COALESCE(SUM(distance), 0)
FROM gk_moves
WHERE geokret = geokret_id
INTO total;

UPDATE gk_geokrety
SET distance = total
WHERE gk_geokrety.id = geokret_id;

RETURN total;
END;$$;


--
-- Name: geokret_compute_total_places_visited(bigint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.geokret_compute_total_places_visited(geokret_id bigint) RETURNS bigint
    LANGUAGE plpgsql
    AS $$DECLARE
total integer;
BEGIN

SELECT COALESCE(COUNT(DISTINCT("position")), 0)
FROM gk_moves
WHERE geokret = geokret_id
INTO total;

UPDATE gk_geokrety
SET caches_count = total
WHERE gk_geokrety.id = geokret_id;

RETURN total;
END;$$;


--
-- Name: geokret_current_holder(bigint, timestamp with time zone); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.geokret_current_holder(geokret_id bigint, since timestamp with time zone DEFAULT NULL::timestamp with time zone) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
DECLARE
	gk gk_geokrety%ROWTYPE;
	last_move gk_moves%ROWTYPE;
	last_author bigint;
BEGIN

-- Load GeoKret
SELECT *
INTO gk
FROM gk_geokrety
WHERE id = geokret_id;

-- Load Last Position
IF since IS NULL THEN
	SELECT *
	INTO last_move
	FROM gk_moves
	WHERE gk_moves.id = gk.last_position;
ELSE
	SELECT *
	INTO last_move
	FROM gk_moves
	WHERE geokret = geokret_id
	AND move_type = ANY (moves_type_last_position())
	AND moved_on_datetime < since
	ORDER BY moved_on_datetime DESC
	LIMIT 1;
END IF;

IF (last_move IS NULL) THEN
	-- NO Move
	last_author := gk.owner;
ELSEIF (last_move.move_type = ANY (geokrety.moves_type_hold())) THEN
	-- Move type hold
	last_author := last_move.author;
ELSEIF last_move.move_type = 3::smallint THEN
	-- Type Seen has recursive check
	SELECT geokret_current_holder(geokret_id, last_move.moved_on_datetime)
	INTO last_author;
ELSEIF last_move.move_type = 4::smallint THEN
	-- Type Archive
	last_author := NULL;
ELSE
	last_author := NULL;
END IF;

-- Ensure user exists
SELECT "id"
INTO last_author
FROM geokrety.gk_users
WHERE "id" = last_author;

RETURN last_author;
END;
$$;


--
-- Name: geokret_gkid(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.geokret_gkid() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

IF (NEW.gkid IS NOT NULL) THEN
	RETURN NEW;
END IF;

SELECT COALESCE(MAX(gkid) + 1, 1) FROM gk_geokrety INTO NEW.gkid;

RETURN NEW;
END;$$;


--
-- Name: geokret_manage_birth_date(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.geokret_manage_birth_date() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
_move gk_moves;
BEGIN

IF (TG_OP = 'INSERT') THEN
    NEW.born_on_datetime = NEW.created_on_datetime;
ELSIF (TG_OP = 'UPDATE') THEN
    IF NEW.born_on_datetime > NOW() THEN
        RAISE EXCEPTION 'GeoKret birth date cannot be greater than current time: %', NOW();
    END IF;

    SELECT *
    FROM geokrety.gk_moves
    WHERE moved_on_datetime < NEW.born_on_datetime
    AND gk_moves.geokret = NEW.id
    ORDER BY moved_on_datetime ASC
    LIMIT 1
    INTO _move;

    IF _move.id IS NOT NULL THEN
        RAISE EXCEPTION 'GeoKret birth date cannot be greater than its oldest move: %', _move.moved_on_datetime;
    END IF;
END IF;
RETURN NEW;
END;
$$;


--
-- Name: geokret_manage_holder(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.geokret_manage_holder() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

IF (TG_OP = 'INSERT') THEN
	NEW.holder = NEW.owner;
ELSE
	SELECT geokret_current_holder(NEW.id)
	INTO NEW.holder;
END IF;

RETURN NEW;
END;
$$;


--
-- Name: geokret_tracking_code(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.geokret_tracking_code() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

IF NEW.tracking_code IS NOT NULL THEN
    NEW.tracking_code = UPPER(NEW.tracking_code);
    IF is_tracking_code_valid(NEW.tracking_code) THEN
        RETURN NEW;
    END IF;
    RAISE 'Tracking code is invalid';
END IF;

NEW.tracking_code = generate_tracking_code();

RETURN NEW;
END;
$$;


--
-- Name: geokrety_compute_last_log(bigint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.geokrety_compute_last_log(geokret_id bigint) RETURNS bigint
    LANGUAGE sql
    AS $$SELECT id
FROM "gk_moves"
WHERE geokret = geokret_id
ORDER BY moved_on_datetime DESC
LIMIT 1;$$;


--
-- Name: geokrety_compute_last_log_and_last_position(bigint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.geokrety_compute_last_log_and_last_position(geokret_id bigint) RETURNS bigint[]
    LANGUAGE plpgsql
    AS $$DECLARE
var_last_log bigint;
var_last_position bigint;
BEGIN

-- find last log
SELECT id
FROM "gk_moves"
WHERE geokret = geokret_id
ORDER BY moved_on_datetime DESC
LIMIT 1
INTO var_last_log;

-- find last move
SELECT id
FROM "gk_moves"
WHERE geokret = geokret_id
AND move_type = ANY (moves_type_last_position())
ORDER BY moved_on_datetime DESC
LIMIT 1
INTO var_last_position;

-- update GeoKret
UPDATE gk_geokrety
SET last_log = var_last_log,
	last_position = var_last_position
WHERE id = geokret_id;

RETURN ARRAY[var_last_log, var_last_position];
END;$$;


--
-- Name: geokrety_compute_last_position(bigint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.geokrety_compute_last_position(geokret_id bigint) RETURNS bigint
    LANGUAGE sql
    AS $$SELECT id
FROM "gk_moves"
WHERE geokret = geokret_id
AND move_type = ANY (moves_type_last_position())
ORDER BY moved_on_datetime DESC
LIMIT 1;$$;


--
-- Name: geokrety_stats_updater(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.geokrety_stats_updater() RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
	counter double precision;
BEGIN

SELECT count(*)
FROM gk_geokrety
INTO counter;

INSERT INTO gk_statistics_counters(name, value)
VALUES('stat_geokretow', 1)
ON CONFLICT (name)
DO UPDATE SET value = counter WHERE gk_statistics_counters.name = 'stat_geokretow';

END;
$$;


--
-- Name: gkdecrypt(bytea, character varying, character varying, integer); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.gkdecrypt(val bytea, secret character varying, psw character varying, key_id integer DEFAULT 1) RETURNS character varying
    LANGUAGE plpgsql
    AS $$DECLARE
gpg_key bytea;
BEGIN

IF val IS NULL THEN
	RETURN NULL;
END IF;

SELECT public.dearmor(public.pgp_sym_decrypt(privatekey::bytea, secret))
FROM secure.gpg_keys
WHERE id = key_id
INTO gpg_key;

RETURN public.pgp_pub_decrypt(val::bytea, gpg_key, psw);

END;$$;


--
-- Name: gkencrypt(text, integer); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.gkencrypt(val text, key_id integer DEFAULT 1) RETURNS bytea
    LANGUAGE plpgsql
    AS $$DECLARE
gpg_key bytea;
BEGIN

SELECT public.dearmor(pubkey)
FROM secure.gpg_keys
WHERE id = key_id
INTO gpg_key;

RETURN public.pgp_pub_encrypt(val, gpg_key);

END;$$;


--
-- Name: invalid_starting_tracking_code(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.invalid_starting_tracking_code() RETURNS character varying[]
    LANGUAGE sql
    AS $$
SELECT '{"GK", "GC", "OP", "OK", "GE", "OZ", "OU", "ON", "OL", "OJ", "OS", "GD", "GA", "VI", "MS", "TR", "EX", "GR", "RH", "OX", "OB", "OR", "LT", "LV"}'::character varying[]
$$;


--
-- Name: is_tracking_code_valid(character varying); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.is_tracking_code_valid(tracking_code character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
BEGIN

IF LENGTH(tracking_code) < 6 THEN
    RETURN FALSE;
ELSIF UPPER(SUBSTRING(tracking_code, 1, 2)) = ANY (invalid_starting_tracking_code()) THEN
    RETURN FALSE;
END IF;

RETURN TRUE;
END;
$$;


--
-- Name: mails_token_generate(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.mails_token_generate() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

IF (TG_OP = 'INSERT' AND NEW.token IS NOT NULL) THEN
	RETURN NEW;
END IF;

IF (TG_OP = 'UPDATE' AND NEW.token IS DISTINCT FROM OLD.token) THEN
	RAISE 'Token cannot be updated';
END IF;

NEW.token = generate_verification_token(10);

RETURN NEW;
END;$$;


--
-- Name: manage_email(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.manage_email() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

IF OLD._email_crypt IS DISTINCT FROM NEW._email_crypt THEN
	RAISE '_email_crypt must not be manually updated';
END IF;
IF OLD._email_hash IS DISTINCT FROM NEW._email_hash THEN
	RAISE '_email_hash must not be manually updated';
END IF;

IF NEW._email IS NULL OR NEW._email = '' THEN
	NEW._email_crypt = NULL;
	NEW._email_hash = NULL;
ELSE
	NEW._email_crypt = gkencrypt(NEW._email::character varying);
	NEW._email_hash = public.digest(lower(NEW._email::character varying), 'sha256');
END IF;

-- Ensure email field is always NULL
NEW._email = NULL;

RETURN NEW;
END;
$$;


--
-- Name: manage_previous_email(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.manage_previous_email() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

IF OLD._previous_email_crypt IS DISTINCT FROM NEW._previous_email_crypt THEN
	RAISE '_previous_email_crypt must not be manually updated';
END IF;
IF OLD._previous_email_hash IS DISTINCT FROM NEW._previous_email_hash THEN
	RAISE '_previous_email_hash must not be manually updated';
END IF;

IF NEW._previous_email IS NOT NULL THEN
	NEW._previous_email_crypt = gkencrypt(NEW._previous_email);
	NEW._previous_email_hash = public.digest(NEW._previous_email, 'sha256');
END IF;

-- Ensure previouse_mail field is always NULL
NEW._previous_email = NULL;

RETURN NEW;
END;$$;


--
-- Name: map_account_status(smallint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.map_account_status(status smallint) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
BEGIN

IF status = 0 THEN
	RETURN 'Non activated';
ELSIF status = 1 THEN
	RETURN 'Active';
ELSIF status = 2 THEN
	RETURN 'Imported';
END IF;

RAISE 'Unknown account status';
END;
$$;


--
-- Name: map_geokrety_types(smallint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.map_geokrety_types(type smallint) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
BEGIN

IF type = 0 THEN
	RETURN 'Traditional';
ELSIF type = 1 THEN
	RETURN 'Book/CD/DVD…';
ELSIF type = 2 THEN
	RETURN 'Human/Pet';
ELSIF type = 3 THEN
	RETURN 'Coin';
ELSIF type = 4 THEN
	RETURN 'KretyPost';
END IF;

RAISE 'Unknown GeoKrety type';
END;
$$;


--
-- Name: map_move_comments_types(smallint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.map_move_comments_types(type smallint) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
BEGIN

IF type = 0 THEN
	RETURN 'Comment';
ELSIF type = 1 THEN
	RETURN 'Missing';
END IF;

RAISE 'Unknown Move Comment type';
END;
$$;


--
-- Name: map_move_types(smallint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.map_move_types(type smallint) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
BEGIN

IF type = 0 THEN
	RETURN 'Dropped';
ELSIF type = 1 THEN
	RETURN 'Grabbed';
ELSIF type = 2 THEN
	RETURN 'Comment';
ELSIF type = 3 THEN
	RETURN 'Seen';
ELSIF type = 4 THEN
	RETURN 'Archived';
ELSIF type = 5 THEN
	RETURN 'Visiting';
END IF;

RAISE 'Unknown Move type';
END;
$$;


--
-- Name: map_pictures_types(smallint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.map_pictures_types(type smallint) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
BEGIN

IF type = 0 THEN
	RETURN 'GK Avatar';
ELSIF type = 1 THEN
	RETURN 'GK Move';
ELSIF type = 2 THEN
	RETURN 'User Avatar';
END IF;

RAISE 'Unknown Picture type';
END;
$$;


--
-- Name: move_counting_kilometers(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.move_counting_kilometers() RETURNS smallint[]
    LANGUAGE sql
    AS $$SELECT '{0,3,5}'::smallint[]$$;


--
-- Name: move_or_moves_comments_manage_geokret_missing(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.move_or_moves_comments_manage_geokret_missing() RETURNS trigger
    LANGUAGE plpgsql
    AS $$DECLARE
v_last_position bigint;
v_missing boolean;
BEGIN

IF (TG_OP = 'DELETE' OR TG_OP = 'UPDATE') THEN
	SELECT last_position
	FROM gk_geokrety
	WHERE gk_geokrety.id = OLD.geokret
	INTO v_last_position;

	SELECT geokret_compute_missing(v_last_position)
	INTO v_missing;

	UPDATE gk_geokrety
	SET missing = v_missing
	WHERE gk_geokrety.id = OLD.geokret;

	IF (TG_OP = 'DELETE') THEN
		RETURN OLD;
	END IF;
END IF;

SELECT last_position
FROM gk_geokrety
WHERE gk_geokrety.id = NEW.geokret
INTO v_last_position;

SELECT geokret_compute_missing(v_last_position)
INTO v_missing;

if (v_missing) THEN
	PERFORM
	FROM gk_moves
	WHERE id = v_last_position
	AND validate_moves_comments_missing(move_type);
END IF;

UPDATE gk_geokrety
SET missing = v_missing
WHERE gk_geokrety.id = NEW.geokret;

RETURN NEW;
END;$$;


--
-- Name: move_requiring_coordinates(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.move_requiring_coordinates() RETURNS smallint[]
    LANGUAGE sql
    AS $$SELECT '{0,3,5}'::smallint[]$$;


--
-- Name: move_type_count_kilometers(smallint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.move_type_count_kilometers(move_type smallint) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN
	IF NOT(move_type = ANY (valid_move_types())) THEN
		RAISE 'Invalid move-type';
	ELSIF move_type = ANY (move_counting_kilometers()) THEN
		RETURN true;
	END IF;
	RETURN false;
END;$$;


--
-- Name: move_type_require_coordinates(smallint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.move_type_require_coordinates(move_type smallint) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN
	IF NOT(move_type = ANY (geokrety.valid_move_types())) THEN
		RAISE 'Invalid move-type';
	ELSIF move_type = ANY (geokrety.move_requiring_coordinates()) THEN
		RETURN true;
	END IF;
	RETURN false;
END;$$;


--
-- Name: moves_check_archive_author(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_check_archive_author() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

IF (NEW.move_type != 4::smallint) THEN
	RETURN NEW;
END IF;

IF (SELECT COUNT(*) > 0 FROM gk_geokrety WHERE id = NEW.geokret AND owner = NEW.author) THEN
	RETURN NEW;
END IF;

RAISE 'Only GeoKret owner can archive it s GeoKrety';

END;
$$;


--
-- Name: moves_check_author_username(bigint, character varying); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_check_author_username(author_id bigint, username character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN

if (author_id IS NOT NULL AND username IS NULL) THEN
	RETURN TRUE;
ELSIF (author_id IS NULL AND username IS NOT NULL AND username != '') THEN
	RETURN TRUE;
END IF;

RETURN FALSE;
END;$$;


--
-- Name: moves_check_waypoint(smallint, character varying); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_check_waypoint(move_type smallint, waypoint character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN

if (move_type = ANY (move_requiring_coordinates()) AND waypoint IS NOT NULL) THEN
	RETURN TRUE;
--ELSIF (author_id IS NULL AND username != '') THEN
--	RETURN TRUE;
END IF;

RAISE 'waypoint must be be null for move_type %', move_type;
END;$$;


--
-- Name: moves_comments_count_on_move_update(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_comments_count_on_move_update() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

IF (TG_OP = 'DELETE' OR TG_OP = 'UPDATE') THEN
	PERFORM moves_count_comments(OLD.move);

	IF (TG_OP = 'DELETE') THEN
		RETURN OLD;
	END IF;
END IF;

PERFORM moves_count_comments(NEW.move);

RETURN NEW;
END;$$;


--
-- Name: moves_comments_manage_geokret(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_comments_manage_geokret() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

SELECT geokret
FROM "gk_moves"
WHERE "gk_moves".id = NEW.move
INTO NEW.geokret;

RETURN NEW;
END;$$;


--
-- Name: moves_comments_missing_only_on_last_position(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_comments_missing_only_on_last_position() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

IF (NEW.type = 1 AND (SELECT COUNT(*) = 0
FROM gk_geokrety
WHERE gk_geokrety.id = NEW.geokret
AND last_position = NEW.move)) THEN
	RAISE '`missing` can only be set on last move position: gk:% mov:% type:%', NEW.geokret, NEW.move, NEW.type;
END IF;

RETURN NEW;
END;$$;


--
-- Name: moves_count_comments(bigint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_count_comments(move_id bigint) RETURNS integer
    LANGUAGE plpgsql
    AS $$DECLARE
total bigint;
BEGIN

SELECT COUNT(*)
FROM gk_moves_comments
WHERE "move" = move_id
INTO total;

UPDATE gk_moves
SET comments_count = total
WHERE id = move_id
AND comments_count != total; -- prevent recursion

RETURN total;
END;$$;


--
-- Name: moves_distances_after(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_distances_after() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

IF (TG_OP = 'INSERT' OR TG_OP = 'UPDATE') THEN
	IF (OLD.geokret != NEW.geokret) THEN
    -- Updating old position
		PERFORM update_next_move_distance(OLD.geokret, OLD.id, true);
		PERFORM geokret_compute_total_places_visited(OLD.geokret);
		PERFORM geokret_compute_total_distance(OLD.geokret);
	END IF;
	PERFORM update_next_move_distance(NEW.geokret, NEW.id);
END IF;

IF (TG_OP = 'DELETE') THEN
	PERFORM geokret_compute_total_distance(OLD.geokret);
	PERFORM geokret_compute_total_places_visited(OLD.geokret);
	RETURN OLD;
END IF;

PERFORM geokret_compute_total_distance(NEW.geokret);
PERFORM geokret_compute_total_places_visited(NEW.geokret);

RETURN NEW;
END;
$$;


--
-- Name: moves_distances_before(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_distances_before() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN
RAISE 'UNUSED?';

IF (TG_OP = 'DELETE') THEN
	SELECT update_next_move_distance(OLD.geokret, OLD.id, true);
	RETURN OLD;
END IF;

IF (TG_OP = 'UPDATE') THEN
    -- Updating old position
	PERFORM update_next_move_distance(OLD.geokret, OLD.id, true);
END IF;

RETURN NEW;
END;$$;


--
-- Name: FUNCTION moves_distances_before(); Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON FUNCTION geokrety.moves_distances_before() IS 'The old position';


--
-- Name: moves_get_on_page(bigint, bigint, bigint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_get_on_page(move_id bigint, per_page bigint DEFAULT 10, geokret_id bigint DEFAULT NULL::bigint) RETURNS bigint
    LANGUAGE plpgsql
    AS $$DECLARE
_geokret_id bigint;
_page bigint;
BEGIN

IF geokret_id IS NULL THEN
	SELECT geokret
	FROM gk_moves
	WHERE id = move_id
	INTO _geokret_id;
ELSE
	_geokret_id = geokret_id;
END IF;

SELECT CEILING((rank - 1) / per_page) + 1 AS page
FROM (
  SELECT id, RANK() OVER (ORDER BY moved_on_datetime DESC)
  FROM "gk_moves"
  WHERE geokret = _geokret_id
  ORDER BY moved_on_datetime ASC
) as ranked
WHERE id = move_id
INTO _page;

RETURN _page;
END;$$;


--
-- Name: moves_gis_updates(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_gis_updates() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
_position public.geography;
_positions RECORD;
country	varchar(2);
elevation integer;
BEGIN

-- Synchronize lat/lon - position
IF (OLD.lat IS DISTINCT FROM NEW.lat OR OLD.lon IS DISTINCT FROM NEW.lon) THEN
	SELECT * FROM coords2position(NEW.lat, NEW.lon) INTO _position;
	NEW.position := _position;
ELSIF (OLD.position IS DISTINCT FROM NEW.position) THEN
	SELECT * FROM position2coords(NEW.position) INTO _positions;
	NEW.lat := _positions.lat;
	NEW.lon := _positions.lon;
END IF;

IF (NEW.position IS NULL) THEN
	NEW.country := NULL;
	NEW.elevation := NULL;
	RETURN NEW;
END IF;

-- Find country / elevation
IF (OLD.position IS DISTINCT FROM NEW.position) THEN
	--SELECT iso_a2
	--FROM public.countries
	--WHERE public.ST_Intersects(geom, NEW.position)
	--INTO country;

	SELECT iso_a2
	FROM public.countries
	WHERE public.ST_Intersects(geom::public.geometry, NEW.position::public.geometry)
	INTO country;

-- geometry
	SELECT public.ST_Value(rast, NEW.position::public.geometry) As elevation
	FROM public.srtm
	WHERE public.ST_Intersects(rast, NEW.position::public.geometry)
	INTO elevation;

	NEW.country := LOWER(country);
	NEW.elevation := elevation;
END IF;

RETURN NEW;
END;
$$;


--
-- Name: moves_log_type_and_position(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_log_type_and_position() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

IF (TG_OP = 'DELETE' OR TG_OP = 'UPDATE') THEN
	IF (OLD.geokret IS DISTINCT FROM NEW.geokret) THEN
		-- Updating old position
		PERFORM geokrety_compute_last_log_and_last_position(OLD.geokret);
	END IF;
	IF (TG_OP = 'DELETE') THEN
		RETURN OLD;
	END IF;
END IF;

PERFORM geokrety_compute_last_log_and_last_position(NEW.geokret);

RETURN NEW;
END;$$;


--
-- Name: moves_manage_geokret_holder(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_manage_geokret_holder() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

-- Force GeoKret to recompute holder
UPDATE gk_geokrety
SET holder = NULL
WHERE id = NEW.geokret;

RETURN NEW;
END;
$$;


--
-- Name: moves_moved_on_datetime_checker(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_moved_on_datetime_checker() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
_geokret gk_geokrety;
BEGIN

SELECT *
FROM gk_geokrety
WHERE id = NEW.geokret
INTO _geokret;

-- move before GK birth
IF DATE_TRUNC('MINUTE', NEW.moved_on_datetime) < DATE_TRUNC('MINUTE', _geokret.born_on_datetime) THEN
	RAISE 'Move date (%) time can not be before GeoKret birth (%)', DATE_TRUNC('MINUTE', NEW.moved_on_datetime), DATE_TRUNC('MINUTE', _geokret.born_on_datetime);
-- move after NOW()
ELSIF NEW.moved_on_datetime > NOW()::timestamp(0) THEN
	RAISE 'The date is in the future (if you are an inventor of a time travelling machine, contact us please!)';
-- same move on this GK at this datetime
ELSIF COUNT(*) > 0 FROM gk_moves WHERE moved_on_datetime = NEW.moved_on_datetime AND "geokret" = NEW.geokret AND id != NEW.id THEN
	RAISE 'A move at the exact same date already exists for this GeoKret';
END IF;

RETURN NEW;
END;
$$;


--
-- Name: moves_moved_on_datetime_updater(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_moved_on_datetime_updater() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN
	IF (TG_OP = 'INSERT' AND NEW.moved_on_datetime is NULL) THEN
		NEW.moved_on_datetime = NEW.created_on_datetime;
	END IF;

	RETURN NEW;
END;$$;


--
-- Name: moves_stats_updater(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_stats_updater() RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
	_moves_count double precision;
	_distance double precision;
	_distance_avg double precision;
	_distance_med double precision;
	_distance_sun double precision;
	_distance_moon double precision;
	_distance_equator double precision;
	_distance_median double precision;
	_distance_average double precision;
	_geokrety_in_cache integer;
BEGIN

SELECT count(*)
FROM gk_moves
INTO _moves_count;

-- counters
INSERT INTO gk_statistics_counters(name, value)
VALUES('stat_ruchow', 1)
ON CONFLICT (name)
DO UPDATE SET value = _moves_count WHERE gk_statistics_counters.name = 'stat_ruchow';

-- distance
WITH moves AS (
    SELECT
        COALESCE(SUM(distance), 0::bigint) AS _dist,
        COALESCE(AVG(distance), 0) AS _dist_avg,
        COALESCE(percentile_disc(0.5) within group (order by distance), 0) AS _dist_med
    FROM gk_moves
    WHERE move_type IN (0, 3, 5)
)
SELECT
    _dist,
    _dist_avg,
    _dist_med,
    ROUND((_dist/40075.0), 2) AS _distance_equator,
    ROUND((_dist/384400.0), 3) AS _distance_moon,
    ROUND((_dist/149597870.7), 5) AS _distance_sun
FROM moves
INTO _distance, _distance_avg, _distance_med, _distance_equator, _distance_moon, _distance_sun;

-- geokrety in caches
SELECT count(*)
FROM "gk_geokrety" LEFT JOIN "gk_moves" ON "gk_geokrety".last_position = "gk_moves".id
WHERE "gk_moves"."move_type" IN (0,3)
INTO _geokrety_in_cache;

-- -- Would have been nice to use the materialized view, but it's not refreshed yet?
        -- SELECT count(*)
-- FROM "gk_geokrety_in_caches"
    -- INTO _geokrety_in_cache;

INSERT INTO gk_statistics_counters(name, value)
VALUES('stat_geokretow_zakopanych', _geokrety_in_cache)
ON CONFLICT (name)
DO UPDATE SET value = _geokrety_in_cache WHERE gk_statistics_counters.name = 'stat_geokretow_zakopanych';

INSERT INTO gk_statistics_counters(name, value)
VALUES('stat_droga', _distance)
ON CONFLICT (name)
DO UPDATE SET value = _distance WHERE gk_statistics_counters.name = 'stat_droga';

-- Equator 40075 km
INSERT INTO gk_statistics_counters(name, value)
VALUES('stat_droga_obwod', _distance_equator)
ON CONFLICT (name)
DO UPDATE SET value = _distance_equator WHERE gk_statistics_counters.name = 'stat_droga_obwod';

-- Moon 384400 km
INSERT INTO gk_statistics_counters(name, value)
VALUES('stat_droga_ksiezyc', _distance_moon)
ON CONFLICT (name)
DO UPDATE SET value = _distance_moon WHERE gk_statistics_counters.name = 'stat_droga_ksiezyc';

-- Sun 149597870.7 km
INSERT INTO gk_statistics_counters(name, value)
VALUES('stat_droga_slonce', _distance_sun)
ON CONFLICT (name)
DO UPDATE SET value = _distance_sun WHERE gk_statistics_counters.name = 'stat_droga_slonce';

-- AVG distance
INSERT INTO gk_statistics_counters(name, value)
VALUES('droga_srednia', _distance_avg)
ON CONFLICT (name)
DO UPDATE SET value = _distance_avg WHERE gk_statistics_counters.name = 'droga_srednia';

-- MEDIAN distance
INSERT INTO gk_statistics_counters(name, value)
VALUES('droga_mediana', _distance_med)
ON CONFLICT (name)
DO UPDATE SET value = _distance_med WHERE gk_statistics_counters.name = 'droga_mediana';

END;
$$;


--
-- Name: moves_type_change(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_type_change() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN
	UPDATE gk_pictures
	SET geokret = NEW.geokret
	WHERE geokret = OLD.geokret
	AND type = 1 -- Move
	AND move = NEW.id;

	RETURN NEW;
END;$$;


--
-- Name: moves_type_hold(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_type_hold() RETURNS smallint[]
    LANGUAGE sql
    AS $$
SELECT '{1,5}'::smallint[]
$$;


--
-- Name: moves_type_last_position(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_type_last_position() RETURNS smallint[]
    LANGUAGE sql
    AS $$SELECT '{0,1,3,4,5}'::smallint[]$$;


--
-- Name: moves_type_waypoint(smallint, character varying); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_type_waypoint(move_type smallint, waypoint character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN

IF NOT(move_type = ANY (geokrety.move_requiring_coordinates())) AND waypoint IS NOT NULL THEN
	RAISE 'waypoint must be null when move_type is %', "move_type";
END IF;

RETURN TRUE;
END;$$;


--
-- Name: moves_types_markable_as_missing(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_types_markable_as_missing() RETURNS smallint[]
    LANGUAGE sql
    AS $$SELECT '{0,3}'::smallint[]$$;


--
-- Name: moves_waypoint_uppercase(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.moves_waypoint_uppercase() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF NEW.waypoint = '' THEN
        NEW.waypoint = NULL;
    END IF;

    NEW.waypoint = UPPER(NEW.waypoint);
    RETURN NEW;
END;
$$;


--
-- Name: news_comments_count_on_news_update(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.news_comments_count_on_news_update() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

IF (TG_OP = 'DELETE' OR TG_OP = 'UPDATE') THEN
	PERFORM news_compute_news_comments_count(OLD.news);

	IF (TG_OP = 'DELETE') THEN
		RETURN OLD;
	END IF;
END IF;

PERFORM news_compute_news_comments_count(NEW.news);

RETURN NEW;
END;$$;


--
-- Name: news_comments_counts_override(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.news_comments_counts_override() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

PERFORM news_compute_news_comments_count(NEW.id);

RETURN NEW;
END;$$;


--
-- Name: news_compute_news_comments_count(bigint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.news_compute_news_comments_count(news_id bigint) RETURNS integer
    LANGUAGE plpgsql
    AS $$DECLARE
total bigint;
BEGIN

SELECT COUNT(*)
FROM gk_news_comments
WHERE news = news_id
INTO total;

UPDATE gk_news
SET comments_count = total
WHERE id = news_id
AND comments_count != total; -- prevent recursion

RETURN total;
END;$$;


--
-- Name: on_update_current_timestamp(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.on_update_current_timestamp() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN NEW.updated_on_datetime = now(); RETURN NEW; END;$$;


--
-- Name: owner_code_check_only_one_active_per_geokret(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.owner_code_check_only_one_active_per_geokret() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

IF COUNT(*) > 1 FROM "gk_owner_codes" WHERE geokret = NEW.geokret AND used = 0 THEN
       RAISE 'An owner code for this GeoKret already exists';
END IF;

RETURN NEW;
END;$$;


--
-- Name: owner_code_check_validating_ip(inet, smallint, timestamp with time zone, bigint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.owner_code_check_validating_ip(validating_ip inet, used smallint, claimed_on_datetime timestamp with time zone, "user" bigint) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN

IF used = 0 AND validating_ip IS NULL AND claimed_on_datetime IS NULL AND "user" IS NULL THEN
	RETURN TRUE;
ELSIF used = 1::smallint AND validating_ip IS NOT NULL AND claimed_on_datetime IS NOT NULL THEN
	RETURN TRUE;
END IF;

RETURN FALSE;
END;$$;


--
-- Name: owner_code_token_generate(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.owner_code_token_generate() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

IF NEW.token IS NOT NULL THEN
	RETURN NEW;
END IF;

NEW.token = generate_adoption_token(6);

RETURN NEW;
END;$$;


--
-- Name: password_check_validating_ip(inet, smallint, timestamp with time zone); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.password_check_validating_ip(validating_ip inet, used smallint, used_on_datetime timestamp with time zone) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN

IF used = 0 AND validating_ip IS NULL AND used_on_datetime IS NULL THEN
	RETURN TRUE;
ELSIF used = 1::smallint AND validating_ip IS NOT NULL AND used_on_datetime IS NOT NULL THEN
	RETURN TRUE;
END IF;

RETURN FALSE;
END;$$;


--
-- Name: password_token_generate(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.password_token_generate() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

IF (OLD.token IS NOT NULL AND NEW.token IS DISTINCT FROM OLD.token) THEN
	RAISE 'Token cannot be updated';
END IF;

NEW.token = generate_password_token(42);

RETURN NEW;
END;$$;


--
-- Name: picture_type_to_table_name(bigint, bigint, bigint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.picture_type_to_table_name(geokret bigint DEFAULT NULL::bigint, move bigint DEFAULT NULL::bigint, "user" bigint DEFAULT NULL::bigint, OUT table_name character varying, OUT id bigint, OUT type smallint) RETURNS record
    LANGUAGE plpgsql
    AS $$BEGIN

RAISE 'DEAD CODE `picture_type_to_table_name()`';

	IF (geokret IS NOT NULL AND move IS NULL AND "user" IS NULL) THEN
		table_name := 'gk_geokrety';
		id := geokret;
		type := 0;
		RETURN;
	ELSIF (geokret IS NULL AND move IS NOT NULL AND "user" IS NULL) THEN
		table_name := 'gk_moves';
		id := move;
		type := 1;
		RETURN;
	ELSIF (geokret IS NULL AND move IS NULL AND "user" IS NOT NULL) THEN
		table_name := 'gk_users';
		id := "user";
		type := 2;
		RETURN;
	ELSIF (geokret IS NOT NULL AND move IS NOT NULL AND "user" IS NULL) THEN
		table_name := 'gk_moves';
		id := move;
		type := 1;
		RETURN;
	ELSIF (geokret IS NULL AND move IS NULL AND "user" IS NULL) THEN
		RAISE 'One of Geokret (%), Move (%) or User (%) must be specified', geokret, move, "user" USING ERRCODE = 'data_exception';
	END IF;

	RAISE 'Only one of Geokret, Move or User must be specified' USING ERRCODE = 'data_exception';

END;$$;


--
-- Name: pictures_counter(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.pictures_counter() RETURNS trigger
    LANGUAGE plpgsql
    AS $$DECLARE
	counter smallint;
BEGIN

IF (TG_OP = 'DELETE' OR TG_OP = 'UPDATE') THEN
	IF (OLD.type = 0) THEN
		UPDATE gk_geokrety
		SET pictures_count = v_table.total
		FROM (SELECT COUNT(*) AS total
			  FROM gk_pictures
			  WHERE "geokret" = OLD.geokret
			  AND "move" IS NULL
			  AND uploaded_on_datetime IS NOT NULL
			  ) AS v_table
		WHERE gk_geokrety.id = OLD.geokret;
	ELSIF (OLD.type = 1) THEN
		UPDATE gk_moves
		SET pictures_count = v_table.total
		FROM (SELECT COUNT(*) AS total
			  FROM gk_pictures
		      WHERE "move" = OLD.move
		      AND "geokret" IS NOT NULL
			  AND uploaded_on_datetime IS NOT NULL
			  ) AS v_table
		WHERE gk_moves.id = OLD.move;
	ELSIF (OLD.type = 2) THEN
		UPDATE gk_users
		SET pictures_count = v_table.total
		FROM (SELECT COUNT(*) AS total
			  FROM gk_pictures
			  WHERE "user" = OLD.user
			  AND uploaded_on_datetime IS NOT NULL
			  ) AS v_table
		WHERE gk_users.id = OLD.user;
	END IF;

	IF (TG_OP = 'DELETE') THEN
		RETURN OLD;
	END IF;
END IF;

IF (NEW.type = 0) THEN
	UPDATE gk_geokrety
	SET pictures_count = v_table.total
	FROM (SELECT COUNT(*) AS total
		  FROM gk_pictures
		  WHERE "geokret" = NEW.geokret
		  AND "move" IS NULL
		  AND uploaded_on_datetime IS NOT NULL
		  ) AS v_table
	WHERE gk_geokrety.id = NEW.geokret;
ELSIF (NEW.type = 1) THEN
	UPDATE gk_moves
	SET pictures_count = v_table.total
	FROM (SELECT COUNT(*) AS total
		  FROM gk_pictures
		  WHERE "move" = NEW.move
		  AND "geokret" IS NOT NULL
		  AND uploaded_on_datetime IS NOT NULL
		  ) AS v_table
	WHERE gk_moves.id = NEW.move;
ELSIF (NEW.type = 2) THEN
	UPDATE gk_users
	SET pictures_count = v_table.total
	FROM (SELECT COUNT(*) AS total
		  FROM gk_pictures
		  WHERE "user" = NEW.user
		  AND uploaded_on_datetime IS NOT NULL
		  ) AS v_table
	WHERE gk_users.id = NEW.user;
END IF;

RETURN NEW;
END;$$;


--
-- Name: pictures_set_featured(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.pictures_set_featured() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
	PICTURE_GEOKRET_AVATAR bigint := 0;
	PICTURE_GEOKRET_MOVE bigint := 1;
	PICTURE_USER_AVATAR bigint := 2;
BEGIN

IF NEW.type = PICTURE_GEOKRET_AVATAR THEN
	UPDATE "gk_geokrety"
	SET "avatar" = NEW.id
	WHERE "id" = NEW.geokret
	AND "avatar" IS NULL;
ELSIF NEW.type = PICTURE_USER_AVATAR THEN
	UPDATE "gk_users"
	SET "avatar" = NEW.id
	WHERE "id" = NEW.user
	AND "avatar" IS NULL;
END IF;

-- No featured images for move pictures

RETURN NEW;
END;
$$;


--
-- Name: pictures_type_updater(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.pictures_type_updater() RETURNS trigger
    LANGUAGE plpgsql
    AS $$DECLARE
	ret	RECORD;
	geokret_id	bigint;
BEGIN

--SELECT * FROM picture_type_to_table_name(NEW.geokret, NEW.move, NEW.user) INTO ret;
--NEW.type := ret.type;

PERFORM validate_picture_type_against_parameters(NEW);

--IF (NEW.type != OLD.type AND NEW.type = 1) THEN
IF (NEW.type = 1) THEN
	SELECT geokret FROM gk_moves WHERE id=NEW.move INTO geokret_id;
	NEW.geokret := geokret_id;
END IF;

RETURN NEW;

END;$$;


--
-- Name: position2coords(public.geography, integer); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.position2coords("position" public.geography, OUT lat double precision, OUT lon double precision, srid integer DEFAULT 4326) RETURNS record
    LANGUAGE sql
    AS $$SELECT public.ST_Y(position::public.geometry) as lat,
       public.ST_X(position::public.geometry) as lon;$$;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: gk_users_authentication_history; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_users_authentication_history (
    id bigint NOT NULL,
    created_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    "user" bigint,
    username text,
    user_agent text,
    ip inet NOT NULL,
    succeed boolean NOT NULL,
    session character varying(255) NOT NULL,
    comment character varying(255),
    method geokrety.authentication_method NOT NULL
);


--
-- Name: previous_failed_logins(text); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.previous_failed_logins(user_name text) RETURNS SETOF geokrety.gk_users_authentication_history
    LANGUAGE plpgsql
    AS $$
DECLARE
    last_auth bigint;
BEGIN

-- Get last authentication result
SELECT CAST(CAST(succeed AS INT) AS BIGINT)
FROM geokrety.gk_users_authentication_history AS uah
WHERE uah."username" = user_name
ORDER BY "created_on_datetime" DESC
LIMIT 1
INTO last_auth;

-- Find previously failed attempts
RETURN QUERY WITH user_attempts AS (
    SELECT *
    FROM geokrety.gk_users_authentication_history AS uah
    WHERE uah."username" = user_name
    ORDER BY "created_on_datetime" DESC
    OFFSET last_auth
),
last_login AS (
    (
        SELECT created_on_datetime
        FROM user_attempts
        WHERE succeed IS TRUE
        LIMIT 1
    )
    UNION ALL
    (
        SELECT '1970-01-01' AS created_on_datetime
    )
    ORDER BY created_on_datetime DESC
    LIMIT 1
)
SELECT *
FROM user_attempts AS ua
WHERE succeed IS FALSE
AND ua.created_on_datetime > (SELECT created_on_datetime FROM last_login);

END;
$$;


--
-- Name: random_between(integer, integer); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.random_between(low integer, high integer) RETURNS integer
    LANGUAGE plpgsql STRICT
    AS $$BEGIN
	RETURN floor(random()* (high-low + 1) + low);
END;$$;


--
-- Name: save_gc_waypoints(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.save_gc_waypoints() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

IF TRIM(NEW.waypoint) = '' OR NEW.waypoint IS NULL OR UPPER(SUBSTR(NEW.waypoint, 1, 2)) != 'GC' THEN
	RETURN NEW;
ELSIF COUNT(*) > 0 FROM gk_waypoints_gc WHERE waypoint = NEW.waypoint THEN
	-- TODO what to do if coordinates changed? update?
	--UPDATE "gk_waypoints_gc"
	--SET "position" = NEW.position
	--WHERE waypoint = NEW.waypoint;

	RETURN NEW;
END IF;

INSERT INTO gk_waypoints_gc ("waypoint", "country", "elevation", "position")
VALUES (NEW.waypoint, NEW.country, NEW.elevation, NEW.position);

RETURN NEW;
END;$$;


--
-- Name: scripts_manage_ack(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.scripts_manage_ack() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

-- cannot ack non locked script
IF (OLD.acked_on_datetime IS NULL AND NEW.acked_on_datetime IS NOT NULL AND NEW.locked_on_datetime IS NULL) THEN
    RAISE EXCEPTION 'cannot ack non locked script';
END IF;

-- ON unlock also unack
IF (NEW.locked_on_datetime IS NULL) THEN
    NEW.acked_on_datetime = NULL;
END IF;

RETURN NEW;
END;
$$;


--
-- Name: session_on_behalf_random(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.session_on_behalf_random() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

SELECT md5(random()::text)
INTO NEW.on_behalf;

RETURN NEW;
END;
$$;


--
-- Name: stats_updater_geokrety(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.stats_updater_geokrety() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
PERFORM geokrety_stats_updater();
RETURN NEW;
END;
$$;


--
-- Name: stats_updater_moves(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.stats_updater_moves() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
PERFORM moves_stats_updater();
RETURN NEW;
END;
$$;


--
-- Name: stats_updater_users(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.stats_updater_users() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
PERFORM users_stats_updater();
RETURN NEW;
END;
$$;


--
-- Name: update_next_move_distance(bigint, bigint, boolean); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.update_next_move_distance(geokret_id bigint, move_id bigint, exclude_current boolean DEFAULT false) RETURNS smallint
    LANGUAGE plpgsql
    AS $$DECLARE
updated_rows smallint;
BEGIN

-- Reset distance for
UPDATE gk_moves
SET distance = NULL
WHERE geokret = geokret_id
AND NOT move_type_count_kilometers(move_type)
AND distance IS NOT NULL; -- Skip if not changed

WITH cte AS (
	-- Compute distances
	SELECT
		id, distance, moved_on_datetime,
		COALESCE(ROUND(public.ST_Distance(position, LAG(position, 1) OVER (ORDER BY moved_on_datetime ASC), false) / 1000), 0) AS new_distance
	FROM gk_moves
	WHERE geokret = geokret_id
	AND move_type_count_kilometers(move_type)
	-- AND (CASE WHEN exclude_current = TRUE THEN move_id ELSE NULL END) IS DISTINCT FROM id -- Allow ignore current row in compute
	ORDER BY moved_on_datetime DESC
)
-- Update rows
UPDATE gk_moves
SET distance = cte.new_distance
FROM cte
WHERE gk_moves.id = cte.id
--AND gk_moves.distance IS DISTINCT FROM cte.new_distance -- Skip if not changed ; also help to prevent recursion
;

RETURN NULL;
END;$$;


--
-- Name: user_delete_anonymize(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.user_delete_anonymize() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

UPDATE gk_moves
SET username = 'Deleted user', author = NULL
WHERE author = OLD.id;

RETURN OLD;
END;
$$;


--
-- Name: FUNCTION user_delete_anonymize(); Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON FUNCTION geokrety.user_delete_anonymize() IS 'Set username as deleted user.';


--
-- Name: user_manage_home_position(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.user_manage_home_position() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
_position public.geography;
_positions RECORD;
_country varchar(2);
BEGIN

-- Home as 0 0 is considered disabled
IF ((NEW.home_latitude = 0 AND NEW.home_longitude = 0) OR
   (NEW.home_latitude IS NULL OR NEW.home_longitude IS NULL)) AND
   (OLD.home_latitude IS DISTINCT FROM NEW.home_latitude OR OLD.home_longitude IS DISTINCT FROM NEW.home_longitude)
   THEN
    NEW.home_latitude := NULL;
    NEW.home_longitude := NULL;
    NEW.home_position := NULL;
    NEW.home_country := NULL;
    NEW.observation_area := NULL;
    RETURN NEW;
END IF;

-- Synchronize lat/lon - position
IF (OLD.home_latitude IS DISTINCT FROM NEW.home_latitude OR OLD.home_longitude IS DISTINCT FROM NEW.home_longitude) OR (NEW.home_latitude IS NOT NULL AND NEW.home_longitude IS NOT NULL AND NEW.home_position IS NULL) THEN
	SELECT * FROM coords2position(NEW.home_latitude, NEW.home_longitude) INTO _position;
	NEW.home_position := _position;
ELSIF (OLD.home_position IS DISTINCT FROM NEW.home_position) THEN
	SELECT * FROM position2coords(NEW.home_position) INTO _positions;
	NEW.home_latitude := _positions.lat;
	NEW.home_longitude := _positions.lon;
END IF;

-- Find country
IF (OLD.home_position IS DISTINCT FROM NEW.home_position) OR (OLD.home_country IS DISTINCT FROM NEW.home_country) THEN
	SELECT iso_a2
	FROM public.countries
	WHERE public.ST_Intersects(geom::public.geometry, NEW.home_position::public.geometry)
	INTO _country;
	NEW.home_country := LOWER(_country);
END IF;

RETURN NEW;
END;
$$;


--
-- Name: user_manage_observation_area(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.user_manage_observation_area() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

-- <= 0 is NULL
IF NEW.observation_area <= 0 THEN
    NEW.observation_area := NULL;
END IF;

RETURN NEW;
END;
$$;


--
-- Name: user_record_username_history(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.user_record_username_history() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

IF OLD.username IS NULL THEN
    RETURN NEW;
END IF;

INSERT INTO gk_users_username_history
    ("user", username_old, username_new)
    VALUES (NEW.id, OLD.username, NEW.username);
RETURN NEW;
END;
$$;


--
-- Name: user_secid_generate(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.user_secid_generate() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

IF OLD._secid_crypt IS DISTINCT FROM NEW._secid_crypt THEN
	RAISE '_secid_crypt must not be manually updated';
END IF;
IF OLD._secid_hash IS DISTINCT FROM NEW._secid_hash THEN
	RAISE '_secid_hash must not be manually updated';
END IF;

IF (NEW._secid IS NULL OR NEW._secid = '') THEN
	NEW._secid = generate_secid(); -- generate a new token
END IF;

NEW._secid_crypt = gkencrypt(NEW._secid);
NEW._secid_hash = public.digest(NEW._secid, 'sha256')::text;
-- Ensure secid field is always NULL
NEW._secid = NULL;

RETURN NEW;
END;$$;


--
-- Name: user_trim_spaces(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.user_trim_spaces() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

SELECT TRIM(regexp_replace(NEW.username, '[\s\v\u0009\u0020\u00A0\u1680\u2000-\u200A\u202F\u205F\u3000]+', ' ', 'g'))
INTO NEW.username;

RETURN NEW;
END;
$$;


--
-- Name: user_username_as_email_not_taken(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.user_username_as_email_not_taken() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
	_username_hash bytea;
BEGIN

_username_hash = public.digest(NEW.username::character varying, 'sha256');

IF (TG_OP = 'UPDATE') THEN
	IF ((SELECT count(*) FROM gk_users WHERE _email_hash = _username_hash) > 0 AND OLD.id != NEW.id) THEN
		RAISE EXCEPTION 'duplicate key value violates unique constraint "gk_users_username_email_uniq"' USING ERRCODE = 'unique_violation';
	END IF;
	RETURN NEW;
END IF;

IF ((SELECT count(*) FROM gk_users WHERE _email_hash = _username_hash) > 0) THEN
	RAISE EXCEPTION 'duplicate key value violates unique constraint "gk_users_username_email_uniq"' USING ERRCODE = 'unique_violation';
END IF;

RETURN NEW;
END;
$$;


--
-- Name: users_stats_updater(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.users_stats_updater() RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
	counter double precision;
BEGIN

SELECT count(*)
FROM gk_users
INTO counter;

INSERT INTO gk_statistics_counters(name, value)
VALUES('stat_userow', 1)
ON CONFLICT (name)
DO UPDATE SET value = counter WHERE gk_statistics_counters.name = 'stat_userow';

END;
$$;


--
-- Name: valid_email_revalidate_used(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.valid_email_revalidate_used() RETURNS smallint[]
    LANGUAGE sql
    AS $$
SELECT '{0,1,2,3}'::smallint[]
$$;


--
-- Name: valid_move_types(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.valid_move_types() RETURNS smallint[]
    LANGUAGE sql
    AS $$SELECT '{0,1,2,3,4,5}'::smallint[]$$;


--
-- Name: valid_moves_comments_types(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.valid_moves_comments_types() RETURNS smallint[]
    LANGUAGE sql
    AS $$SELECT '{0,1}'::smallint[]$$;


--
-- Name: validate_move_types(smallint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.validate_move_types(move_type smallint) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN
	RETURN move_type = ANY (valid_move_types());
END;$$;


--
-- Name: validate_moves_comments_missing(smallint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.validate_moves_comments_missing(move_type smallint) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN

IF (NOT (move_type = ANY (moves_types_markable_as_missing()))) THEN
	RAISE '`missing` status cannot be set for such move type';
END IF;

RETURN TRUE;
END;$$;


--
-- Name: validate_moves_comments_type(smallint); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.validate_moves_comments_type(comment_type smallint) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN
	RETURN comment_type = ANY (valid_moves_comments_types());
END;$$;


--
-- Name: gk_pictures; Type: TABLE; Schema: geokrety; Owner: -
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


--
-- Name: COLUMN gk_pictures.type; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_pictures.type IS 'const PICTURE_GEOKRET_AVATAR = 0; const PICTURE_GEOKRET_MOVE = 1; const PICTURE_USER_AVATAR = 2;';


--
-- Name: validate_picture_type_against_parameters(geokrety.gk_pictures); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.validate_picture_type_against_parameters(row_p geokrety.gk_pictures) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN

IF (row_p.type = 0 AND row_p.geokret IS NOT NULL AND row_p.move IS NULL AND row_p.user IS NULL) THEN
	RETURN true;
ELSIF (row_p.type = 1 AND row_p.geokret IS NULL AND row_p.move IS NOT NULL AND row_p.user IS NULL) THEN
	RETURN true;
ELSIF (row_p.type = 1 AND row_p.geokret IS NOT NULL AND row_p.move IS NOT NULL AND row_p.user IS NULL) THEN
	RETURN true;
ELSIF (row_p.type = 2 AND row_p.geokret IS NULL AND row_p.move IS NULL AND row_p.user IS NOT NULL) THEN
	RETURN true;
ELSIF (row_p.type > 2) THEN
	RAISE 'Picture type unrecognized (%)', row_p.type USING ERRCODE = 'data_exception';
ELSIF (row_p.geokret IS NULL AND row_p.move IS NULL AND row_p.user IS NULL) THEN
	RAISE 'One of Geokret (%), Move (%) or User (%) must be specified', row_p.geokret, row_p.move, row_p.user USING ERRCODE = 'data_exception';
END IF;

RAISE 'Picture `type` does not match the specified arguments.' USING ERRCODE = 'data_exception';

END;$$;


--
-- Name: waypoints_gc_fill_from_moves(); Type: FUNCTION; Schema: geokrety; Owner: -
--

CREATE FUNCTION geokrety.waypoints_gc_fill_from_moves() RETURNS void
    LANGUAGE plpgsql
    AS $$BEGIN

TRUNCATE "gk_waypoints_gc";

ALTER SEQUENCE waypoints_gc_id_seq RESTART WITH 1;

INSERT INTO "gk_waypoints_gc" (waypoint, country, elevation, position, lat, lon)
SELECT DISTINCT ON ("waypoint") "waypoint", "country", "elevation", "position", lat, lon
FROM "gk_moves"
WHERE "waypoint" LIKE 'GC%'
AND "waypoint" <> 'GC'
ORDER BY "waypoint" ASC;

END;$$;


--
-- Name: gk_account_activation; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_account_activation (
    id bigint NOT NULL,
    token character varying(60),
    "user" bigint NOT NULL,
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    used_on_datetime timestamp(0) with time zone,
    requesting_ip inet NOT NULL,
    validating_ip inet,
    used smallint DEFAULT '0'::smallint NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP,
    last_notification_datetime timestamp with time zone,
    CONSTRAINT check_validating_ip CHECK (geokrety.account_activation_check_validating_ip(validating_ip, used)),
    CONSTRAINT validate_used CHECK ((used = ANY (ARRAY[0, 1, 2, 3])))
);


--
-- Name: COLUMN gk_account_activation.used; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_account_activation.used IS '0=unused 1=validated 2=expired 3=disabled';


--
-- Name: account_activation_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.account_activation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: account_activation_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.account_activation_id_seq OWNED BY geokrety.gk_account_activation.id;


--
-- Name: gk_awards_won; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_awards_won (
    id bigint NOT NULL,
    holder bigint,
    description character varying(128) NOT NULL,
    awarded_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP,
    award bigint NOT NULL
);


--
-- Name: badges_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.badges_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: badges_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.badges_id_seq OWNED BY geokrety.gk_awards_won.id;


--
-- Name: gk_email_activation; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_email_activation (
    id bigint NOT NULL,
    token character varying(60),
    revert_token character varying(60),
    "user" bigint NOT NULL,
    _previous_email character varying(150),
    _email character varying(150),
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP,
    used_on_datetime timestamp(0) with time zone,
    reverted_on_datetime timestamp(0) with time zone,
    requesting_ip inet NOT NULL,
    updating_ip inet,
    reverting_ip inet,
    used smallint DEFAULT '0'::smallint NOT NULL,
    _email_crypt bytea NOT NULL,
    _previous_email_crypt bytea,
    _email_hash bytea NOT NULL,
    _previous_email_hash bytea,
    CONSTRAINT not_empty CHECK (((_email)::text <> ''::text)),
    CONSTRAINT validate_used_ip_datetime CHECK (geokrety.email_activation_check_used_ip_datetime(used, updating_ip, used_on_datetime, reverting_ip, reverted_on_datetime)),
    CONSTRAINT validate_used_value CHECK ((used = ANY (ARRAY[0, 1, 2, 3, 4, 5, 6])))
);


--
-- Name: COLUMN gk_email_activation._previous_email; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_email_activation._previous_email IS 'Store the previous in case of needed rollback';


--
-- Name: COLUMN gk_email_activation.used; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_email_activation.used IS 'TOKEN_UNUSED = 0
TOKEN_CHANGED = 1
TOKEN_REFUSED = 2
TOKEN_EXPIRED = 3
TOKEN_DISABLED = 4
TOKEN_VALIDATED = 5
TOKEN_REVERTED = 6';


--
-- Name: email_activation_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.email_activation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: email_activation_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.email_activation_id_seq OWNED BY geokrety.gk_email_activation.id;


--
-- Name: gk_geokrety; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_geokrety (
    id bigint NOT NULL,
    gkid bigint,
    tracking_code character varying(9),
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
    label_template integer,
    legacy_mission text,
    born_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT validate_type CHECK ((type = ANY (ARRAY[0, 1, 2, 3, 4])))
);


--
-- Name: COLUMN gk_geokrety.gkid; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_geokrety.gkid IS 'The real GK id : https://stackoverflow.com/a/33791018/944936';


--
-- Name: COLUMN gk_geokrety.holder; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_geokrety.holder IS 'In the hands of user';


--
-- Name: COLUMN gk_geokrety.missing; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_geokrety.missing IS 'true=missing';


--
-- Name: COLUMN gk_geokrety.type; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_geokrety.type IS '0, 1, 2, 3, 4';


--
-- Name: geokrety_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.geokrety_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: geokrety_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.geokrety_id_seq OWNED BY geokrety.gk_geokrety.id;


--
-- Name: gk_geokrety_rating; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_geokrety_rating (
    id bigint NOT NULL,
    geokret bigint NOT NULL,
    author bigint NOT NULL,
    rate smallint NOT NULL,
    rated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: COLUMN gk_geokrety_rating.rate; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_geokrety_rating.rate IS 'single rating (number of stars)';


--
-- Name: geokrety_rating_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.geokrety_rating_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: geokrety_rating_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.geokrety_rating_id_seq OWNED BY geokrety.gk_geokrety_rating.id;


--
-- Name: gk_awards; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_awards (
    id bigint NOT NULL,
    name character varying(128) NOT NULL,
    created_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    start_on_datetime timestamp with time zone,
    end_on_datetime timestamp with time zone,
    description text NOT NULL,
    filename character varying(128) NOT NULL,
    type geokrety.action_type NOT NULL,
    "group" bigint
);


--
-- Name: gk_awards_group; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_awards_group (
    id bigint NOT NULL,
    name text NOT NULL,
    description text NOT NULL
);


--
-- Name: gk_awards_group_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.gk_awards_group_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gk_awards_group_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.gk_awards_group_id_seq OWNED BY geokrety.gk_awards_group.id;


--
-- Name: gk_awards_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.gk_awards_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gk_awards_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.gk_awards_id_seq OWNED BY geokrety.gk_awards.id;


--
-- Name: gk_email_revalidate; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_email_revalidate (
    id bigint NOT NULL,
    "user" bigint NOT NULL,
    used smallint DEFAULT 0 NOT NULL,
    _email character varying(128),
    token character varying(60),
    created_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    validated_on_datetime timestamp with time zone,
    expired_on_datetime timestamp with time zone,
    disabled_on_datetime timestamp with time zone,
    validating_ip inet,
    _email_crypt bytea NOT NULL,
    _email_hash bytea NOT NULL,
    last_notification_datetime timestamp with time zone,
    CONSTRAINT validate_used CHECK ((used = ANY (geokrety.valid_email_revalidate_used()))),
    CONSTRAINT validated_ip CHECK ((((validating_ip IS NOT NULL) AND (used = 1)) OR ((validating_ip IS NULL) AND (used <> 1))))
);


--
-- Name: COLUMN gk_email_revalidate.used; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_email_revalidate.used IS 'TOKEN_UNUSED = 0
TOKEN_VALIDATED = 1
TOKEN_EXPIRED = 2
TOKEN_DISABLED = 3';


--
-- Name: gk_email_revalidate_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.gk_email_revalidate_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gk_email_revalidate_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.gk_email_revalidate_id_seq OWNED BY geokrety.gk_email_revalidate.id;


--
-- Name: gk_moves; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_moves (
    id bigint NOT NULL,
    geokret bigint NOT NULL,
    lat double precision,
    lon double precision,
    elevation integer DEFAULT '-32768'::integer,
    country character varying(3),
    distance integer,
    waypoint character varying(11),
    author bigint,
    comment character varying(5120),
    pictures_count smallint DEFAULT '0'::smallint NOT NULL,
    comments_count integer DEFAULT 0 NOT NULL,
    username character varying(80),
    app character varying(16),
    app_ver character varying(128),
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    moved_on_datetime timestamp(0) with time zone NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP,
    move_type smallint NOT NULL,
    "position" public.geography,
    CONSTRAINT check_author_username CHECK (geokrety.moves_check_author_username(author, username)),
    CONSTRAINT check_type_waypoint CHECK (geokrety.moves_type_waypoint(move_type, waypoint)),
    CONSTRAINT require_coordinates CHECK (((geokrety.move_type_require_coordinates(move_type) AND (lat IS NOT NULL) AND geokrety.move_type_require_coordinates(move_type) AND (lon IS NOT NULL)) OR ((NOT geokrety.move_type_require_coordinates(move_type)) AND (lat IS NULL) AND (NOT geokrety.move_type_require_coordinates(move_type)) AND (lon IS NULL)))),
    CONSTRAINT validate_logtype CHECK ((move_type = ANY (ARRAY[0, 1, 2, 3, 4, 5, 6])))
);


--
-- Name: COLUMN gk_moves.elevation; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_moves.elevation IS '-32768 when alt cannot be found';


--
-- Name: COLUMN gk_moves.country; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_moves.country IS 'ISO 3166-1 https://fr.wikipedia.org/wiki/ISO_3166-1';


--
-- Name: COLUMN gk_moves.app; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_moves.app IS 'source of the log';


--
-- Name: COLUMN gk_moves.app_ver; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_moves.app_ver IS 'application version/codename';


--
-- Name: COLUMN gk_moves.moved_on_datetime; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_moves.moved_on_datetime IS 'The move as configured by user';


--
-- Name: COLUMN gk_moves.move_type; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_moves.move_type IS '0=drop, 1=grab, 2=comment, 3=met, 4=arch, 5=dip';


--
-- Name: gk_users; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_users (
    id bigint NOT NULL,
    username character varying(80) NOT NULL,
    password character varying(120),
    joined_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP,
    daily_mails boolean DEFAULT true NOT NULL,
    registration_ip inet NOT NULL,
    preferred_language character varying(2),
    home_latitude double precision,
    home_longitude double precision,
    observation_area smallint,
    home_country character varying,
    daily_mails_hour smallint DEFAULT geokrety.random_between(0, 23) NOT NULL,
    avatar bigint,
    pictures_count integer DEFAULT 0 NOT NULL,
    last_mail_datetime timestamp(0) with time zone,
    last_login_datetime timestamp(0) with time zone,
    terms_of_use_datetime timestamp(0) with time zone,
    statpic_template integer DEFAULT 1 NOT NULL,
    email_invalid smallint DEFAULT '0'::smallint NOT NULL,
    account_valid smallint DEFAULT '0'::smallint NOT NULL,
    _secid_crypt bytea,
    _email_crypt bytea,
    _email character varying(128),
    _secid character varying,
    _secid_hash bytea,
    _email_hash bytea,
    home_position public.geography,
    CONSTRAINT validate_account_valid CHECK ((account_valid = ANY (ARRAY[0, 1, 2]))),
    CONSTRAINT validate_email_invalid CHECK ((email_invalid = ANY (ARRAY[0, 1, 2, 3, 4, 5])))
);


--
-- Name: COLUMN gk_users.pictures_count; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_users.pictures_count IS 'Attached avatar count';


--
-- Name: COLUMN gk_users.terms_of_use_datetime; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_users.terms_of_use_datetime IS 'Acceptation date';


--
-- Name: COLUMN gk_users.account_valid; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_users.account_valid IS '0=unconfirmed 1=confirmed';


--
-- Name: COLUMN gk_users._secid_crypt; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_users._secid_crypt IS 'READ ONLY
use _secid for writing';


--
-- Name: COLUMN gk_users._secid; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_users._secid IS 'WRITE ONLY
field for secid';


--
-- Name: gk_geokrety_with_details; Type: VIEW; Schema: geokrety; Owner: -
--

CREATE VIEW geokrety.gk_geokrety_with_details AS
 SELECT gk_geokrety.id,
    gk_geokrety.gkid,
    gk_geokrety.tracking_code,
    gk_geokrety.name,
    gk_geokrety.mission,
    gk_geokrety.owner,
    gk_geokrety.distance,
    gk_geokrety.caches_count,
    gk_geokrety.pictures_count,
    gk_geokrety.last_position,
    gk_geokrety.last_log,
    gk_geokrety.holder,
    gk_geokrety.avatar,
    gk_geokrety.created_on_datetime,
    gk_geokrety.updated_on_datetime,
    gk_geokrety.missing,
    gk_geokrety.type,
    gk_moves."position",
    gk_moves.lat,
    gk_moves.lon,
    gk_moves.waypoint,
    gk_moves.elevation,
    gk_moves.country,
    gk_moves.move_type,
    gk_moves.author,
    gk_moves.moved_on_datetime,
    COALESCE(gk_moves.username, m_author.username) AS author_username,
    COALESCE(g_owner.username, 'Abandoned'::character varying) AS owner_username,
    g_avatar.key AS avatar_key,
    gk_geokrety.born_on_datetime
   FROM ((((geokrety.gk_geokrety
     LEFT JOIN geokrety.gk_moves ON ((gk_geokrety.last_position = gk_moves.id)))
     LEFT JOIN geokrety.gk_users m_author ON ((gk_moves.author = m_author.id)))
     LEFT JOIN geokrety.gk_users g_owner ON ((gk_geokrety.owner = g_owner.id)))
     LEFT JOIN geokrety.gk_pictures g_avatar ON ((gk_geokrety.avatar = g_avatar.id)));


--
-- Name: gk_geokrety_in_caches; Type: MATERIALIZED VIEW; Schema: geokrety; Owner: -
--

CREATE MATERIALIZED VIEW geokrety.gk_geokrety_in_caches AS
 SELECT id,
    gkid,
    tracking_code,
    name,
    mission,
    owner,
    distance,
    caches_count,
    pictures_count,
    last_position,
    last_log,
    holder,
    avatar,
    created_on_datetime,
    updated_on_datetime,
    missing,
    type,
    "position",
    lat,
    lon,
    waypoint,
    elevation,
    country,
    move_type,
    author,
    moved_on_datetime,
    author_username,
    owner_username,
    avatar_key
   FROM geokrety.gk_geokrety_with_details
  WHERE (move_type = ANY (geokrety.moves_types_markable_as_missing()))
  WITH NO DATA;


--
-- Name: gk_geokrety_legacy; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_geokrety_legacy (
    id bigint NOT NULL,
    nr character varying(9) NOT NULL,
    nazwa character varying(75) NOT NULL,
    opis text
);


--
-- Name: gk_geokrety_near_users_homes; Type: VIEW; Schema: geokrety; Owner: -
--

CREATE VIEW geokrety.gk_geokrety_near_users_homes AS
 SELECT c_user.id AS c_user_id,
    c_user.username AS c_username,
    gk_geokrety_in_caches.id,
    gk_geokrety_in_caches.gkid,
    gk_geokrety_in_caches.tracking_code,
    gk_geokrety_in_caches.name,
    gk_geokrety_in_caches.mission,
    gk_geokrety_in_caches.owner,
    gk_geokrety_in_caches.distance,
    gk_geokrety_in_caches.caches_count,
    gk_geokrety_in_caches.pictures_count,
    gk_geokrety_in_caches.last_position,
    gk_geokrety_in_caches.last_log,
    gk_geokrety_in_caches.holder,
    gk_geokrety_in_caches.avatar,
    gk_geokrety_in_caches.created_on_datetime,
    gk_geokrety_in_caches.updated_on_datetime,
    gk_geokrety_in_caches.missing,
    gk_geokrety_in_caches.type,
    gk_geokrety_in_caches."position",
    gk_geokrety_in_caches.lat,
    gk_geokrety_in_caches.lon,
    gk_geokrety_in_caches.waypoint,
    gk_geokrety_in_caches.elevation,
    gk_geokrety_in_caches.country,
    gk_geokrety_in_caches.move_type,
    gk_geokrety_in_caches.author,
    gk_geokrety_in_caches.moved_on_datetime,
    gk_geokrety_in_caches.author_username,
    gk_geokrety_in_caches.owner_username,
    gk_geokrety_in_caches.avatar_key,
    (public.st_distance(gk_geokrety_in_caches."position", geokrety.coords2position(c_user.home_latitude, c_user.home_longitude)) / (1000)::double precision) AS home_distance
   FROM geokrety.gk_geokrety_in_caches,
    geokrety.gk_users c_user
  WHERE public.st_dwithin(gk_geokrety_in_caches."position", geokrety.coords2position(c_user.home_latitude, c_user.home_longitude), ((c_user.observation_area * 1000))::double precision)
  ORDER BY (public.st_distance(gk_geokrety_in_caches."position", geokrety.coords2position(c_user.home_latitude, c_user.home_longitude)) < ((c_user.observation_area * 1000))::double precision);


--
-- Name: gk_labels; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_labels (
    id integer NOT NULL,
    template character varying(128) NOT NULL,
    title character varying(512) NOT NULL,
    author character varying(128) NOT NULL,
    created_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: gk_labels_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.gk_labels_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gk_labels_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.gk_labels_id_seq OWNED BY geokrety.gk_labels.id;


--
-- Name: gk_mails; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_mails (
    id bigint NOT NULL,
    token character varying(10),
    from_user bigint,
    to_user bigint,
    subject character varying(255) NOT NULL,
    content text NOT NULL,
    sent_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    ip inet NOT NULL
);


--
-- Name: gk_moves_comments; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_moves_comments (
    id bigint NOT NULL,
    move bigint NOT NULL,
    geokret bigint,
    author bigint,
    content character varying(500) NOT NULL,
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP,
    type smallint NOT NULL,
    CONSTRAINT validate_type CHECK ((type = ANY (ARRAY[0, 1])))
);


--
-- Name: COLUMN gk_moves_comments.type; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_moves_comments.type IS '0=comment, 1=missing';


--
-- Name: gk_news; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_news (
    id bigint NOT NULL,
    title character varying(128) NOT NULL,
    content text NOT NULL,
    author_name character varying(80),
    author bigint,
    comments_count integer DEFAULT 0 NOT NULL,
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    last_commented_on_datetime timestamp(0) with time zone
);


--
-- Name: gk_news_comments; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_news_comments (
    id bigint NOT NULL,
    news bigint NOT NULL,
    author bigint,
    content character varying(1000) NOT NULL,
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: gk_news_comments_access; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_news_comments_access (
    id bigint NOT NULL,
    news bigint NOT NULL,
    author bigint NOT NULL,
    last_read_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    subscribed boolean NOT NULL
);


--
-- Name: gk_owner_codes; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_owner_codes (
    id bigint NOT NULL,
    geokret bigint NOT NULL,
    token character varying(20),
    generated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    claimed_on_datetime timestamp(0) with time zone,
    adopter bigint,
    validating_ip inet,
    used smallint DEFAULT '0'::smallint NOT NULL
);


--
-- Name: COLUMN gk_owner_codes.used; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_owner_codes.used IS '0=unused 1=used';


--
-- Name: gk_password_tokens; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_password_tokens (
    id bigint NOT NULL,
    token character varying(60),
    "user" bigint NOT NULL,
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    used_on_datetime timestamp(0) with time zone,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP,
    requesting_ip inet NOT NULL,
    used smallint DEFAULT '0'::smallint NOT NULL,
    validating_ip inet,
    CONSTRAINT check_validating_ip CHECK (geokrety.password_check_validating_ip(validating_ip, used, used_on_datetime)),
    CONSTRAINT validate_used CHECK ((used = ANY (ARRAY[0, 1])))
);


--
-- Name: COLUMN gk_password_tokens.used; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_password_tokens.used IS '0=unused 1=used';


--
-- Name: gk_races; Type: TABLE; Schema: geokrety; Owner: -
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
    CONSTRAINT validate_type CHECK (((type)::text = ANY (ARRAY[('wpt'::character varying)::text, ('targetDistance'::character varying)::text, ('targetCaches'::character varying)::text, ('maxDistance'::character varying)::text, ('maxCaches'::character varying)::text])))
);


--
-- Name: COLUMN gk_races.created_on_datetime; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_races.created_on_datetime IS 'Creation date';


--
-- Name: COLUMN gk_races.private; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_races.private IS '0 = public, 1 = private';


--
-- Name: COLUMN gk_races.password; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_races.password IS 'password to join the race';


--
-- Name: COLUMN gk_races.start_on_datetime; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_races.start_on_datetime IS 'Race start date';


--
-- Name: COLUMN gk_races.end_on_datetime; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_races.end_on_datetime IS 'Race end date';


--
-- Name: COLUMN gk_races.target_dist; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_races.target_dist IS 'target distance';


--
-- Name: COLUMN gk_races.target_caches; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_races.target_caches IS 'targeted number of caches';


--
-- Name: COLUMN gk_races.status; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_races.status IS 'race status. 0 = initialized, 1 = persist, 2 = finite, 3 = finished but logs flow down';


--
-- Name: gk_races_participants; Type: TABLE; Schema: geokrety; Owner: -
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


--
-- Name: gk_site_settings; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_site_settings (
    id integer NOT NULL,
    name character varying(64) NOT NULL,
    value character varying(256),
    created_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: gk_site_settings_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.gk_site_settings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gk_site_settings_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.gk_site_settings_id_seq OWNED BY geokrety.gk_site_settings.id;


--
-- Name: gk_site_settings_parameters; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_site_settings_parameters (
    name character varying(64) NOT NULL,
    type character varying(32) DEFAULT 'string'::character varying NOT NULL,
    "default" character varying(256),
    description text,
    created_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: gk_social_auth_providers; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_social_auth_providers (
    id integer NOT NULL,
    name character varying(128) NOT NULL,
    created_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: gk_social_auth_providers_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.gk_social_auth_providers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gk_social_auth_providers_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.gk_social_auth_providers_id_seq OWNED BY geokrety.gk_social_auth_providers.id;


--
-- Name: gk_statistics_counters; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_statistics_counters (
    id integer NOT NULL,
    name character varying(32) NOT NULL,
    value double precision NOT NULL
);


--
-- Name: gk_statistics_counters_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.gk_statistics_counters_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gk_statistics_counters_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.gk_statistics_counters_id_seq OWNED BY geokrety.gk_statistics_counters.id;


--
-- Name: gk_statistics_daily_counters; Type: TABLE; Schema: geokrety; Owner: -
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


--
-- Name: gk_statistics_daily_counters_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.gk_statistics_daily_counters_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gk_statistics_daily_counters_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.gk_statistics_daily_counters_id_seq OWNED BY geokrety.gk_statistics_daily_counters.id;


--
-- Name: gk_users_authentication_history_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.gk_users_authentication_history_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gk_users_authentication_history_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.gk_users_authentication_history_id_seq OWNED BY geokrety.gk_users_authentication_history.id;


--
-- Name: gk_users_settings; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_users_settings (
    id integer NOT NULL,
    "user" bigint NOT NULL,
    name character varying(64) NOT NULL,
    value character varying(256),
    created_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: gk_users_settings_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.gk_users_settings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gk_users_settings_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.gk_users_settings_id_seq OWNED BY geokrety.gk_users_settings.id;


--
-- Name: gk_users_settings_parameters; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_users_settings_parameters (
    name character varying(64) NOT NULL,
    type character varying(32) DEFAULT 'string'::character varying NOT NULL,
    "default" character varying(256),
    description text,
    created_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: gk_users_social_auth; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_users_social_auth (
    id bigint NOT NULL,
    "user" bigint NOT NULL,
    provider integer NOT NULL,
    uid text NOT NULL,
    created_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: gk_users_social_auth_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.gk_users_social_auth_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gk_users_social_auth_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.gk_users_social_auth_id_seq OWNED BY geokrety.gk_users_social_auth.id;


--
-- Name: gk_users_username_history; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_users_username_history (
    id bigint NOT NULL,
    "user" bigint NOT NULL,
    username_old character varying(128) NOT NULL,
    username_new character varying(128) NOT NULL,
    created_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: gk_users_username_history_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.gk_users_username_history_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gk_users_username_history_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.gk_users_username_history_id_seq OWNED BY geokrety.gk_users_username_history.id;


--
-- Name: gk_watched; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_watched (
    id bigint NOT NULL,
    "user" bigint NOT NULL,
    geokret bigint NOT NULL,
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: gk_waypoints_country; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_waypoints_country (
    original character varying(191) NOT NULL,
    country character varying(191)
);


--
-- Name: waypoints_gc_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.waypoints_gc_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gk_waypoints_gc; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_waypoints_gc (
    id bigint DEFAULT nextval('geokrety.waypoints_gc_id_seq'::regclass) NOT NULL,
    waypoint character varying(11) NOT NULL,
    country character varying(3),
    elevation integer,
    "position" public.geography NOT NULL,
    lat double precision,
    lon double precision
);


--
-- Name: gk_waypoints_oc; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_waypoints_oc (
    id bigint NOT NULL,
    waypoint character varying(11) NOT NULL,
    lat double precision NOT NULL,
    lon double precision NOT NULL,
    elevation integer,
    country character varying,
    name character varying(255),
    owner character varying(150),
    type character varying(200),
    country_name character varying(200),
    link character varying(255),
    added_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP,
    status smallint DEFAULT '1'::smallint NOT NULL,
    provider character varying(128),
    "position" public.geography,
    CONSTRAINT validate_status CHECK ((status = ANY (ARRAY[0, 1, 2, 3, 6, 7])))
);


--
-- Name: COLUMN gk_waypoints_oc.country; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_waypoints_oc.country IS 'country code as ISO 3166-1 alpha-2';


--
-- Name: COLUMN gk_waypoints_oc.country_name; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_waypoints_oc.country_name IS 'full English country name';


--
-- Name: COLUMN gk_waypoints_oc.status; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON COLUMN geokrety.gk_waypoints_oc.status IS '0, 1, 2, 3, 6, 7';


--
-- Name: gk_waypoints_sync; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_waypoints_sync (
    service_id character varying(128) NOT NULL,
    revision bigint,
    id integer NOT NULL,
    updated_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    last_success_datetime timestamp with time zone,
    last_error_datetime timestamp with time zone,
    error_count integer DEFAULT 0 NOT NULL,
    wpt_count integer DEFAULT 0 NOT NULL,
    last_error text
);


--
-- Name: TABLE gk_waypoints_sync; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON TABLE geokrety.gk_waypoints_sync IS 'Last synchronization time for GC services';


--
-- Name: gk_waypoints_sync_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.gk_waypoints_sync_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gk_waypoints_sync_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.gk_waypoints_sync_id_seq OWNED BY geokrety.gk_waypoints_sync.id;


--
-- Name: gk_waypoints_types; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_waypoints_types (
    type character varying(255) NOT NULL,
    cache_type character varying(255)
);


--
-- Name: gk_yearly_ranking; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.gk_yearly_ranking (
    id bigint NOT NULL,
    year integer NOT NULL,
    "user" bigint,
    rank integer NOT NULL,
    "group" bigint NOT NULL,
    distance integer,
    count integer NOT NULL,
    award bigint NOT NULL,
    awarded_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: gk_yearly_ranking_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.gk_yearly_ranking_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gk_yearly_ranking_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.gk_yearly_ranking_id_seq OWNED BY geokrety.gk_yearly_ranking.id;


--
-- Name: mails_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.mails_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: mails_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.mails_id_seq OWNED BY geokrety.gk_mails.id;


--
-- Name: move_comments_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.move_comments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: move_comments_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.move_comments_id_seq OWNED BY geokrety.gk_moves_comments.id;


--
-- Name: moves_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.moves_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: moves_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.moves_id_seq OWNED BY geokrety.gk_moves.id;


--
-- Name: news_comments_access_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.news_comments_access_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: news_comments_access_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.news_comments_access_id_seq OWNED BY geokrety.gk_news_comments_access.id;


--
-- Name: news_comments_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.news_comments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: news_comments_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.news_comments_id_seq OWNED BY geokrety.gk_news_comments.id;


--
-- Name: news_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.news_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: news_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.news_id_seq OWNED BY geokrety.gk_news.id;


--
-- Name: owner_codes_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.owner_codes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: owner_codes_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.owner_codes_id_seq OWNED BY geokrety.gk_owner_codes.id;


--
-- Name: password_tokens_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.password_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: password_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.password_tokens_id_seq OWNED BY geokrety.gk_password_tokens.id;


--
-- Name: phinxlog; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.phinxlog (
    version numeric NOT NULL,
    migration_name character varying(100),
    start_time timestamp with time zone,
    end_time timestamp with time zone,
    breakpoint boolean DEFAULT false NOT NULL
);


--
-- Name: pictures_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.pictures_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pictures_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.pictures_id_seq OWNED BY geokrety.gk_pictures.id;


--
-- Name: races_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.races_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: races_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.races_id_seq OWNED BY geokrety.gk_races.id;


--
-- Name: races_participants_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.races_participants_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: races_participants_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.races_participants_id_seq OWNED BY geokrety.gk_races_participants.id;


--
-- Name: scripts; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.scripts (
    id bigint NOT NULL,
    name character varying(128) NOT NULL,
    last_run_datetime timestamp with time zone,
    last_page bigint,
    locked_on_datetime timestamp with time zone,
    acked_on_datetime timestamp with time zone
);


--
-- Name: scripts_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.scripts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: scripts_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.scripts_id_seq OWNED BY geokrety.scripts.id;


--
-- Name: sessions; Type: TABLE; Schema: geokrety; Owner: -
--

CREATE TABLE geokrety.sessions (
    session_id character varying(255) NOT NULL,
    data text,
    ip character varying(45),
    agent character varying(300),
    stamp integer,
    persistent boolean DEFAULT false NOT NULL,
    on_behalf character varying(32) NOT NULL,
    "user" bigint
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.users_id_seq OWNED BY geokrety.gk_users.id;


--
-- Name: watched_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.watched_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: watched_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.watched_id_seq OWNED BY geokrety.gk_watched.id;


--
-- Name: waypoints_oc_id_seq; Type: SEQUENCE; Schema: geokrety; Owner: -
--

CREATE SEQUENCE geokrety.waypoints_oc_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: waypoints_oc_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: -
--

ALTER SEQUENCE geokrety.waypoints_oc_id_seq OWNED BY geokrety.gk_waypoints_oc.id;


--
-- Name: gk_account_activation id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_account_activation ALTER COLUMN id SET DEFAULT nextval('geokrety.account_activation_id_seq'::regclass);


--
-- Name: gk_awards id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_awards ALTER COLUMN id SET DEFAULT nextval('geokrety.gk_awards_id_seq'::regclass);


--
-- Name: gk_awards_group id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_awards_group ALTER COLUMN id SET DEFAULT nextval('geokrety.gk_awards_group_id_seq'::regclass);


--
-- Name: gk_awards_won id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_awards_won ALTER COLUMN id SET DEFAULT nextval('geokrety.badges_id_seq'::regclass);


--
-- Name: gk_email_activation id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_email_activation ALTER COLUMN id SET DEFAULT nextval('geokrety.email_activation_id_seq'::regclass);


--
-- Name: gk_email_revalidate id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_email_revalidate ALTER COLUMN id SET DEFAULT nextval('geokrety.gk_email_revalidate_id_seq'::regclass);


--
-- Name: gk_geokrety id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_geokrety ALTER COLUMN id SET DEFAULT nextval('geokrety.geokrety_id_seq'::regclass);


--
-- Name: gk_geokrety_rating id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_geokrety_rating ALTER COLUMN id SET DEFAULT nextval('geokrety.geokrety_rating_id_seq'::regclass);


--
-- Name: gk_labels id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_labels ALTER COLUMN id SET DEFAULT nextval('geokrety.gk_labels_id_seq'::regclass);


--
-- Name: gk_mails id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_mails ALTER COLUMN id SET DEFAULT nextval('geokrety.mails_id_seq'::regclass);


--
-- Name: gk_moves id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_moves ALTER COLUMN id SET DEFAULT nextval('geokrety.moves_id_seq'::regclass);


--
-- Name: gk_moves_comments id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_moves_comments ALTER COLUMN id SET DEFAULT nextval('geokrety.move_comments_id_seq'::regclass);


--
-- Name: gk_news id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_news ALTER COLUMN id SET DEFAULT nextval('geokrety.news_id_seq'::regclass);


--
-- Name: gk_news_comments id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_news_comments ALTER COLUMN id SET DEFAULT nextval('geokrety.news_comments_id_seq'::regclass);


--
-- Name: gk_news_comments_access id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_news_comments_access ALTER COLUMN id SET DEFAULT nextval('geokrety.news_comments_access_id_seq'::regclass);


--
-- Name: gk_owner_codes id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_owner_codes ALTER COLUMN id SET DEFAULT nextval('geokrety.owner_codes_id_seq'::regclass);


--
-- Name: gk_password_tokens id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_password_tokens ALTER COLUMN id SET DEFAULT nextval('geokrety.password_tokens_id_seq'::regclass);


--
-- Name: gk_pictures id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_pictures ALTER COLUMN id SET DEFAULT nextval('geokrety.pictures_id_seq'::regclass);


--
-- Name: gk_races id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_races ALTER COLUMN id SET DEFAULT nextval('geokrety.races_id_seq'::regclass);


--
-- Name: gk_races_participants id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_races_participants ALTER COLUMN id SET DEFAULT nextval('geokrety.races_participants_id_seq'::regclass);


--
-- Name: gk_site_settings id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_site_settings ALTER COLUMN id SET DEFAULT nextval('geokrety.gk_site_settings_id_seq'::regclass);


--
-- Name: gk_social_auth_providers id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_social_auth_providers ALTER COLUMN id SET DEFAULT nextval('geokrety.gk_social_auth_providers_id_seq'::regclass);


--
-- Name: gk_statistics_counters id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_statistics_counters ALTER COLUMN id SET DEFAULT nextval('geokrety.gk_statistics_counters_id_seq'::regclass);


--
-- Name: gk_statistics_daily_counters id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_statistics_daily_counters ALTER COLUMN id SET DEFAULT nextval('geokrety.gk_statistics_daily_counters_id_seq'::regclass);


--
-- Name: gk_users id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_users ALTER COLUMN id SET DEFAULT nextval('geokrety.users_id_seq'::regclass);


--
-- Name: gk_users_authentication_history id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_users_authentication_history ALTER COLUMN id SET DEFAULT nextval('geokrety.gk_users_authentication_history_id_seq'::regclass);


--
-- Name: gk_users_settings id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_users_settings ALTER COLUMN id SET DEFAULT nextval('geokrety.gk_users_settings_id_seq'::regclass);


--
-- Name: gk_users_social_auth id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_users_social_auth ALTER COLUMN id SET DEFAULT nextval('geokrety.gk_users_social_auth_id_seq'::regclass);


--
-- Name: gk_users_username_history id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_users_username_history ALTER COLUMN id SET DEFAULT nextval('geokrety.gk_users_username_history_id_seq'::regclass);


--
-- Name: gk_watched id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_watched ALTER COLUMN id SET DEFAULT nextval('geokrety.watched_id_seq'::regclass);


--
-- Name: gk_waypoints_oc id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_waypoints_oc ALTER COLUMN id SET DEFAULT nextval('geokrety.waypoints_oc_id_seq'::regclass);


--
-- Name: gk_waypoints_sync id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_waypoints_sync ALTER COLUMN id SET DEFAULT nextval('geokrety.gk_waypoints_sync_id_seq'::regclass);


--
-- Name: gk_yearly_ranking id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_yearly_ranking ALTER COLUMN id SET DEFAULT nextval('geokrety.gk_yearly_ranking_id_seq'::regclass);


--
-- Name: scripts id; Type: DEFAULT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.scripts ALTER COLUMN id SET DEFAULT nextval('geokrety.scripts_id_seq'::regclass);


--
-- Name: gk_owner_codes check_validating_ip; Type: CHECK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE geokrety.gk_owner_codes
    ADD CONSTRAINT check_validating_ip CHECK (geokrety.owner_code_check_validating_ip(validating_ip, used, claimed_on_datetime, adopter)) NOT VALID;


--
-- Name: gk_awards_group gk_awards_group_pkey; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_awards_group
    ADD CONSTRAINT gk_awards_group_pkey PRIMARY KEY (id);


--
-- Name: gk_awards gk_awards_pkey; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_awards
    ADD CONSTRAINT gk_awards_pkey PRIMARY KEY (id);


--
-- Name: gk_awards_won gk_awards_won_holder_award; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_awards_won
    ADD CONSTRAINT gk_awards_won_holder_award UNIQUE (holder, award);


--
-- Name: gk_geokrety gk_geokrety_primary; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT gk_geokrety_primary PRIMARY KEY (id);


--
-- Name: gk_labels gk_labels_pkey; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_labels
    ADD CONSTRAINT gk_labels_pkey PRIMARY KEY (id);


--
-- Name: gk_mails gk_mails_token_uniq; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_mails
    ADD CONSTRAINT gk_mails_token_uniq UNIQUE (token);


--
-- Name: gk_news_comments_access gk_news_comments_access_news_author; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_news_comments_access
    ADD CONSTRAINT gk_news_comments_access_news_author UNIQUE (news, author);


--
-- Name: gk_news_comments_access gk_news_comments_access_news_pkey; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_news_comments_access
    ADD CONSTRAINT gk_news_comments_access_news_pkey PRIMARY KEY (id);


--
-- Name: gk_pictures gk_pictures_id; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_pictures
    ADD CONSTRAINT gk_pictures_id PRIMARY KEY (id);


--
-- Name: gk_site_settings_parameters gk_site_settings_parameters_pkey; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_site_settings_parameters
    ADD CONSTRAINT gk_site_settings_parameters_pkey PRIMARY KEY (name);


--
-- Name: gk_site_settings gk_site_settings_pkey; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_site_settings
    ADD CONSTRAINT gk_site_settings_pkey PRIMARY KEY (id);


--
-- Name: gk_social_auth_providers gk_social_auth_providers_pkey; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_social_auth_providers
    ADD CONSTRAINT gk_social_auth_providers_pkey PRIMARY KEY (id);


--
-- Name: gk_statistics_counters gk_statistics_counters_id; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_statistics_counters
    ADD CONSTRAINT gk_statistics_counters_id PRIMARY KEY (id);


--
-- Name: gk_statistics_daily_counters gk_statistics_daily_counters_date; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_statistics_daily_counters
    ADD CONSTRAINT gk_statistics_daily_counters_date UNIQUE (date);


--
-- Name: gk_statistics_daily_counters gk_statistics_daily_counters_id; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_statistics_daily_counters
    ADD CONSTRAINT gk_statistics_daily_counters_id PRIMARY KEY (id);


--
-- Name: gk_users_authentication_history gk_users_authentication_history_pkey; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_users_authentication_history
    ADD CONSTRAINT gk_users_authentication_history_pkey PRIMARY KEY (id);


--
-- Name: gk_users_settings_parameters gk_users_settings_parameters_pkey; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_users_settings_parameters
    ADD CONSTRAINT gk_users_settings_parameters_pkey PRIMARY KEY (name);


--
-- Name: gk_users_settings gk_users_settings_pkey; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_users_settings
    ADD CONSTRAINT gk_users_settings_pkey PRIMARY KEY (id);


--
-- Name: gk_users_social_auth gk_users_social_auth_pkey; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_users_social_auth
    ADD CONSTRAINT gk_users_social_auth_pkey PRIMARY KEY (id);


--
-- Name: gk_users_username_history gk_users_username_history_pkey; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_users_username_history
    ADD CONSTRAINT gk_users_username_history_pkey PRIMARY KEY (id);


--
-- Name: gk_waypoints_gc gk_waypoints_gc_id; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_waypoints_gc
    ADD CONSTRAINT gk_waypoints_gc_id PRIMARY KEY (id);


--
-- Name: gk_waypoints_gc gk_waypoints_gc_waypoint; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_waypoints_gc
    ADD CONSTRAINT gk_waypoints_gc_waypoint UNIQUE (waypoint);


--
-- Name: gk_waypoints_sync gk_waypoints_sync_pkey; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_waypoints_sync
    ADD CONSTRAINT gk_waypoints_sync_pkey PRIMARY KEY (id);


--
-- Name: gk_waypoints_sync gk_waypoints_sync_service_id; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_waypoints_sync
    ADD CONSTRAINT gk_waypoints_sync_service_id UNIQUE (service_id);


--
-- Name: gk_waypoints_types gk_waypoints_types_type; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_waypoints_types
    ADD CONSTRAINT gk_waypoints_types_type UNIQUE (type);


--
-- Name: gk_yearly_ranking gk_yearly_ranking_pkey; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_yearly_ranking
    ADD CONSTRAINT gk_yearly_ranking_pkey PRIMARY KEY (id);


--
-- Name: gk_yearly_ranking gk_yearly_ranking_uniq; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_yearly_ranking
    ADD CONSTRAINT gk_yearly_ranking_uniq UNIQUE (year, "user", award);


--
-- Name: gk_account_activation idx_20969_primary; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_account_activation
    ADD CONSTRAINT idx_20969_primary PRIMARY KEY (id);


--
-- Name: gk_awards_won idx_20984_primary; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_awards_won
    ADD CONSTRAINT idx_20984_primary PRIMARY KEY (id);


--
-- Name: gk_email_activation idx_20991_primary; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_email_activation
    ADD CONSTRAINT idx_20991_primary PRIMARY KEY (id);


--
-- Name: gk_geokrety_rating idx_21016_primary; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_geokrety_rating
    ADD CONSTRAINT idx_21016_primary PRIMARY KEY (id);


--
-- Name: gk_moves_comments idx_21034_primary; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_moves_comments
    ADD CONSTRAINT idx_21034_primary PRIMARY KEY (id);


--
-- Name: gk_moves idx_21044_primary; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_moves
    ADD CONSTRAINT idx_21044_primary PRIMARY KEY (id);


--
-- Name: gk_news idx_21058_primary; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_news
    ADD CONSTRAINT idx_21058_primary PRIMARY KEY (id);


--
-- Name: gk_news_comments idx_21069_primary; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_news_comments
    ADD CONSTRAINT idx_21069_primary PRIMARY KEY (id);


--
-- Name: gk_owner_codes idx_21085_code; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_owner_codes
    ADD CONSTRAINT idx_21085_code UNIQUE (token);


--
-- Name: gk_owner_codes idx_21085_primary; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_owner_codes
    ADD CONSTRAINT idx_21085_primary PRIMARY KEY (id);


--
-- Name: gk_password_tokens idx_21092_primary; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_password_tokens
    ADD CONSTRAINT idx_21092_primary PRIMARY KEY (id);


--
-- Name: gk_races idx_21114_primary; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_races
    ADD CONSTRAINT idx_21114_primary PRIMARY KEY (id);


--
-- Name: gk_users idx_21135_primary; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_users
    ADD CONSTRAINT idx_21135_primary PRIMARY KEY (id);


--
-- Name: gk_watched idx_21153_primary; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_watched
    ADD CONSTRAINT idx_21153_primary PRIMARY KEY (id);


--
-- Name: gk_waypoints_oc idx_21160_primary; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_waypoints_oc
    ADD CONSTRAINT idx_21160_primary PRIMARY KEY (id);


--
-- Name: phinxlog idx_21180_primary; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.phinxlog
    ADD CONSTRAINT idx_21180_primary PRIMARY KEY (version);


--
-- Name: scripts idx_21189_primary; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.scripts
    ADD CONSTRAINT idx_21189_primary PRIMARY KEY (id);


--
-- Name: gk_email_revalidate idx_id_primary; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_email_revalidate
    ADD CONSTRAINT idx_id_primary PRIMARY KEY (id);


--
-- Name: sessions sessions_on_behalf; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.sessions
    ADD CONSTRAINT sessions_on_behalf UNIQUE (on_behalf);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (session_id);


--
-- Name: gk_geokrety_created_on_datetime; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_geokrety_created_on_datetime ON geokrety.gk_geokrety USING btree (created_on_datetime);


--
-- Name: gk_geokrety_distance; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_geokrety_distance ON geokrety.gk_geokrety USING btree (distance);


--
-- Name: gk_geokrety_in_caches_position; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_geokrety_in_caches_position ON geokrety.gk_geokrety_in_caches USING gist ("position");


--
-- Name: gk_labels_template; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_labels_template ON geokrety.gk_labels USING btree (template);


--
-- Name: gk_labels_title; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_labels_title ON geokrety.gk_labels USING btree (title);


--
-- Name: gk_moves_author; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_moves_author ON geokrety.gk_moves USING btree (author);


--
-- Name: gk_moves_country; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_moves_country ON geokrety.gk_moves USING btree (country);


--
-- Name: gk_moves_country_index; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_moves_country_index ON geokrety.gk_moves USING btree (country);


--
-- Name: gk_moves_created_on_datetime; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_moves_created_on_datetime ON geokrety.gk_moves USING btree (created_on_datetime);


--
-- Name: gk_moves_elevation; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_moves_elevation ON geokrety.gk_moves USING btree (elevation);


--
-- Name: gk_moves_geokret_id; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_moves_geokret_id ON geokrety.gk_moves USING btree (geokret);


--
-- Name: gk_moves_lat; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_moves_lat ON geokrety.gk_moves USING btree (lat);


--
-- Name: gk_moves_lon; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_moves_lon ON geokrety.gk_moves USING btree (lon);


--
-- Name: gk_moves_move_type; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_moves_move_type ON geokrety.gk_moves USING btree (move_type);


--
-- Name: gk_moves_move_type_distance; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_moves_move_type_distance ON geokrety.gk_moves USING btree (move_type, distance);


--
-- Name: gk_moves_move_type_id; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_moves_move_type_id ON geokrety.gk_moves USING btree (move_type, id);


--
-- Name: gk_moves_move_type_id_position; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_moves_move_type_id_position ON geokrety.gk_moves USING btree (move_type, id, "position");


--
-- Name: gk_moves_moved_on_datetime; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_moves_moved_on_datetime ON geokrety.gk_moves USING btree (moved_on_datetime);


--
-- Name: gk_moves_type_index; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_moves_type_index ON geokrety.gk_moves USING btree (move_type);


--
-- Name: gk_moves_updated_on_datetime; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_moves_updated_on_datetime ON geokrety.gk_moves USING btree (updated_on_datetime);


--
-- Name: gk_moves_waypoint; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_moves_waypoint ON geokrety.gk_moves USING btree (waypoint);


--
-- Name: gk_pictures_filename; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_pictures_filename ON geokrety.gk_pictures USING btree (filename);


--
-- Name: gk_pictures_key; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_pictures_key ON geokrety.gk_pictures USING btree (key);


--
-- Name: gk_pictures_uploaded_on_datetime_move_geokret; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_pictures_uploaded_on_datetime_move_geokret ON geokrety.gk_pictures USING btree (uploaded_on_datetime, move, geokret);


--
-- Name: gk_pictures_uploaded_on_datetime_user; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_pictures_uploaded_on_datetime_user ON geokrety.gk_pictures USING btree (uploaded_on_datetime, "user");


--
-- Name: gk_site_settings_name; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE UNIQUE INDEX gk_site_settings_name ON geokrety.gk_site_settings USING btree (name);


--
-- Name: gk_social_auth_providers_name; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_social_auth_providers_name ON geokrety.gk_social_auth_providers USING btree (name);


--
-- Name: gk_users_authentication_history_succeed; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_users_authentication_history_succeed ON geokrety.gk_users_authentication_history USING btree (succeed);


--
-- Name: gk_users_authentication_history_username; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_users_authentication_history_username ON geokrety.gk_users_authentication_history USING btree (username);


--
-- Name: gk_users_authentication_history_username_succeed; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_users_authentication_history_username_succeed ON geokrety.gk_users_authentication_history USING btree (username, succeed);


--
-- Name: gk_users_email_uniq; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_users_email_uniq ON geokrety.gk_users USING btree (_email_hash);


--
-- Name: INDEX gk_users_email_uniq; Type: COMMENT; Schema: geokrety; Owner: -
--

COMMENT ON INDEX geokrety.gk_users_email_uniq IS 'NOTE: Uniq should be active but we have many accounts with duplicated emails:

select email_old, count(*)
from gk_users
group by email_old
HAVING count(*) > 1';


--
-- Name: gk_users_secid_uniq; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE UNIQUE INDEX gk_users_secid_uniq ON geokrety.gk_users USING btree (_secid_hash);


--
-- Name: gk_users_settings_user_name; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE UNIQUE INDEX gk_users_settings_user_name ON geokrety.gk_users_settings USING btree ("user", name);


--
-- Name: gk_users_social_auth_uid; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_users_social_auth_uid ON geokrety.gk_users_social_auth USING btree (uid);


--
-- Name: gk_users_social_auth_user_provider; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE UNIQUE INDEX gk_users_social_auth_user_provider ON geokrety.gk_users_social_auth USING btree ("user", provider);


--
-- Name: gk_users_username_uniq; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE UNIQUE INDEX gk_users_username_uniq ON geokrety.gk_users USING btree (lower((username)::text));


--
-- Name: gk_watched_user_geokret; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE UNIQUE INDEX gk_watched_user_geokret ON geokrety.gk_watched USING btree ("user", geokret);


--
-- Name: gk_waypoints_waypoint; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_waypoints_waypoint ON geokrety.gk_waypoints_oc USING btree (waypoint);


--
-- Name: gk_yearly_ranking_year; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX gk_yearly_ranking_year ON geokrety.gk_yearly_ranking USING btree (year);


--
-- Name: id_type_position; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX id_type_position ON geokrety.gk_moves USING btree (move_type, id, "position");


--
-- Name: idx_20984_timestamp; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_20984_timestamp ON geokrety.gk_awards_won USING btree (awarded_on_datetime);


--
-- Name: idx_20984_userid; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_20984_userid ON geokrety.gk_awards_won USING btree (holder);


--
-- Name: idx_20991_token; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_20991_token ON geokrety.gk_email_activation USING btree (token);


--
-- Name: idx_20991_user; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_20991_user ON geokrety.gk_email_activation USING btree ("user");


--
-- Name: idx_21016_geokret; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21016_geokret ON geokrety.gk_geokrety_rating USING btree (geokret);


--
-- Name: idx_21016_user; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21016_user ON geokrety.gk_geokrety_rating USING btree (author);


--
-- Name: idx_21024_from; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21024_from ON geokrety.gk_mails USING btree (from_user);


--
-- Name: idx_21024_id_maila; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE UNIQUE INDEX idx_21024_id_maila ON geokrety.gk_mails USING btree (id);


--
-- Name: idx_21024_to; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21024_to ON geokrety.gk_mails USING btree (to_user);


--
-- Name: idx_21034_kret_id; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21034_kret_id ON geokrety.gk_moves_comments USING btree (geokret);


--
-- Name: idx_21034_ruch_id; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21034_ruch_id ON geokrety.gk_moves_comments USING btree (move);


--
-- Name: idx_21034_user_id; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21034_user_id ON geokrety.gk_moves_comments USING btree (author);


--
-- Name: idx_21044_alt; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21044_alt ON geokrety.gk_moves USING btree (elevation);


--
-- Name: idx_21044_data; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21044_data ON geokrety.gk_moves USING btree (created_on_datetime);


--
-- Name: idx_21044_data_dodania; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21044_data_dodania ON geokrety.gk_moves USING btree (moved_on_datetime);


--
-- Name: idx_21044_lat; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21044_lat ON geokrety.gk_moves USING btree (lat);


--
-- Name: idx_21044_lon; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21044_lon ON geokrety.gk_moves USING btree (lon);


--
-- Name: idx_21044_timestamp; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21044_timestamp ON geokrety.gk_moves USING btree (updated_on_datetime);


--
-- Name: idx_21044_user; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21044_user ON geokrety.gk_moves USING btree (author);


--
-- Name: idx_21044_waypoint; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21044_waypoint ON geokrety.gk_moves USING btree (waypoint);


--
-- Name: idx_21058_date; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21058_date ON geokrety.gk_news USING btree (created_on_datetime);


--
-- Name: idx_21058_userid; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21058_userid ON geokrety.gk_news USING btree (author);


--
-- Name: idx_21069_author; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21069_author ON geokrety.gk_news_comments USING btree (author);


--
-- Name: idx_21069_news; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21069_news ON geokrety.gk_news_comments USING btree (news);


--
-- Name: idx_21079_id; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE UNIQUE INDEX idx_21079_id ON geokrety.gk_news_comments_access USING btree (id);


--
-- Name: idx_21079_user; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21079_user ON geokrety.gk_news_comments_access USING btree (author);


--
-- Name: idx_21085_kret_id; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21085_kret_id ON geokrety.gk_owner_codes USING btree (geokret);


--
-- Name: idx_21085_user; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21085_user ON geokrety.gk_owner_codes USING btree (adopter);


--
-- Name: idx_21092_created_on_datetime; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21092_created_on_datetime ON geokrety.gk_password_tokens USING btree (created_on_datetime);


--
-- Name: idx_21092_user; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21092_user ON geokrety.gk_password_tokens USING btree ("user");


--
-- Name: idx_21114_organizer; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21114_organizer ON geokrety.gk_races USING btree (organizer);


--
-- Name: idx_21125_geokret; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21125_geokret ON geokrety.gk_races_participants USING btree (geokret);


--
-- Name: idx_21125_race; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21125_race ON geokrety.gk_races_participants USING btree (race);


--
-- Name: idx_21125_racegkid; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE UNIQUE INDEX idx_21125_racegkid ON geokrety.gk_races_participants USING btree (id);


--
-- Name: idx_21135_avatar; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21135_avatar ON geokrety.gk_users USING btree (avatar);


--
-- Name: idx_21135_last_login_datetime; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21135_last_login_datetime ON geokrety.gk_users USING btree (last_login_datetime);


--
-- Name: idx_21135_username; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21135_username ON geokrety.gk_users USING btree (username);


--
-- Name: idx_21153_id; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21153_id ON geokrety.gk_watched USING btree (geokret);


--
-- Name: idx_21153_userid; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_21153_userid ON geokrety.gk_watched USING btree ("user");


--
-- Name: idx_21171_unique_kraj; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE UNIQUE INDEX idx_21171_unique_kraj ON geokrety.gk_waypoints_country USING btree (original);


--
-- Name: idx_21189_name; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE UNIQUE INDEX idx_21189_name ON geokrety.scripts USING btree (name);


--
-- Name: idx_geokret_avatar; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_geokret_avatar ON geokrety.gk_geokrety USING btree (avatar);


--
-- Name: idx_geokret_gkid; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE UNIQUE INDEX idx_geokret_gkid ON geokrety.gk_geokrety USING btree (gkid);


--
-- Name: idx_geokret_hands_of; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_geokret_hands_of ON geokrety.gk_geokrety USING btree (holder);


--
-- Name: idx_geokret_last_log; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_geokret_last_log ON geokrety.gk_geokrety USING btree (last_log);


--
-- Name: idx_geokret_last_position; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_geokret_last_position ON geokrety.gk_geokrety USING btree (last_position);


--
-- Name: idx_geokret_owner; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_geokret_owner ON geokrety.gk_geokrety USING btree (owner);


--
-- Name: idx_geokrety_tracking_code; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE UNIQUE INDEX idx_geokrety_tracking_code ON geokrety.gk_geokrety USING btree (tracking_code);


--
-- Name: idx_geokrety_updated_on_datetime; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_geokrety_updated_on_datetime ON geokrety.gk_geokrety USING btree (updated_on_datetime);


--
-- Name: idx_gk_geokrety_in_caches_id; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE UNIQUE INDEX idx_gk_geokrety_in_caches_id ON geokrety.gk_geokrety_in_caches USING btree (id);


--
-- Name: idx_gk_geokrety_in_caches_moved_on_datetime; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_gk_geokrety_in_caches_moved_on_datetime ON geokrety.gk_geokrety_in_caches USING btree (moved_on_datetime);


--
-- Name: idx_gk_moves_comments_created_on_datetime; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_gk_moves_comments_created_on_datetime ON geokrety.gk_moves_comments USING btree (created_on_datetime);


--
-- Name: idx_moves_geokret; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_moves_geokret ON geokrety.gk_moves USING btree (geokret);


--
-- Name: idx_moves_id; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_moves_id ON geokrety.gk_moves USING btree (id);


--
-- Name: idx_moves_type_id; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_moves_type_id ON geokrety.gk_moves USING btree (move_type, id);


--
-- Name: idx_position; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE INDEX idx_position ON geokrety.gk_moves USING gist ("position");


--
-- Name: idx_token_unique; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE UNIQUE INDEX idx_token_unique ON geokrety.gk_email_revalidate USING btree (token);


--
-- Name: idx_waypoint_unique; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE UNIQUE INDEX idx_waypoint_unique ON geokrety.gk_waypoints_oc USING btree (waypoint);


--
-- Name: name_unique; Type: INDEX; Schema: geokrety; Owner: -
--

CREATE UNIQUE INDEX name_unique ON geokrety.gk_statistics_counters USING btree (name);


--
-- Name: gk_geokrety after_10_create_delete; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER after_10_create_delete AFTER INSERT OR DELETE ON geokrety.gk_geokrety FOR EACH ROW EXECUTE FUNCTION geokrety.stats_updater_geokrety();


--
-- Name: gk_users after_10_create_delete; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER after_10_create_delete AFTER INSERT OR DELETE ON geokrety.gk_users FOR EACH ROW EXECUTE FUNCTION geokrety.stats_updater_users();


--
-- Name: gk_users after_10_disable_all_account_activation; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER after_10_disable_all_account_activation BEFORE INSERT OR UPDATE OF account_valid ON geokrety.gk_users FOR EACH ROW WHEN ((new.account_valid = 1)) EXECUTE FUNCTION geokrety.account_activation_disable_all();


--
-- Name: gk_email_revalidate after_10_only_one_at_a_time; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER after_10_only_one_at_a_time AFTER UPDATE ON geokrety.gk_email_revalidate FOR EACH ROW WHEN ((new.used = 0)) EXECUTE FUNCTION geokrety.email_revalidate_check_only_one_active_per_user();


--
-- Name: gk_pictures after_10_pictures_counter_updater; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER after_10_pictures_counter_updater AFTER INSERT OR DELETE OR UPDATE OF move, geokret, "user", uploaded_on_datetime ON geokrety.gk_pictures FOR EACH ROW EXECUTE FUNCTION geokrety.pictures_counter();


--
-- Name: gk_moves_comments after_10_update_missing; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER after_10_update_missing AFTER INSERT OR DELETE OR UPDATE OF geokret, type ON geokrety.gk_moves_comments FOR EACH ROW EXECUTE FUNCTION geokrety.move_or_moves_comments_manage_geokret_missing();


--
-- Name: gk_moves after_10_update_picture; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER after_10_update_picture AFTER UPDATE OF geokret ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.moves_type_change();


--
-- Name: gk_users after_10_update_username; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER after_10_update_username AFTER UPDATE OF username ON geokrety.gk_users FOR EACH ROW EXECUTE FUNCTION geokrety.user_record_username_history();


--
-- Name: gk_moves after_20_last_log_and_position; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER after_20_last_log_and_position AFTER INSERT OR DELETE OR UPDATE OF geokret, moved_on_datetime, move_type ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.moves_log_type_and_position();


--
-- Name: gk_pictures after_20_set_featured_picture; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER after_20_set_featured_picture AFTER UPDATE OF uploaded_on_datetime ON geokrety.gk_pictures FOR EACH ROW EXECUTE FUNCTION geokrety.pictures_set_featured();


--
-- Name: gk_moves_comments after_20_updates_moves; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER after_20_updates_moves AFTER INSERT OR DELETE OR UPDATE OF move ON geokrety.gk_moves_comments FOR EACH ROW EXECUTE FUNCTION geokrety.moves_comments_count_on_move_update();


--
-- Name: gk_moves after_30_distances; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER after_30_distances AFTER INSERT OR DELETE OR UPDATE OF geokret, lat, lon, moved_on_datetime, move_type, "position" ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.moves_distances_after();


--
-- Name: gk_moves after_40_update_missing; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER after_40_update_missing AFTER INSERT OR DELETE OR UPDATE OF geokret, moved_on_datetime, move_type ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.move_or_moves_comments_manage_geokret_missing();


--
-- Name: gk_moves after_50_manage_waypoint_gc; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER after_50_manage_waypoint_gc AFTER INSERT ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.save_gc_waypoints();


--
-- Name: gk_moves after_70_update_holder; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER after_70_update_holder AFTER INSERT OR DELETE OR UPDATE OF geokret, author, moved_on_datetime, move_type ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.moves_manage_geokret_holder();


--
-- Name: gk_geokrety after_99_notify_amqp; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER after_99_notify_amqp AFTER INSERT OR DELETE OR UPDATE OF name, owner, caches_count, pictures_count, last_position, last_log, holder, avatar, missing, type, mission, distance ON geokrety.gk_geokrety FOR EACH ROW EXECUTE FUNCTION notify_queues.amqp_notify_gkid();


--
-- Name: gk_users after_99_notify_amqp; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER after_99_notify_amqp AFTER INSERT OR DELETE OR UPDATE OF username ON geokrety.gk_users FOR EACH ROW EXECUTE FUNCTION notify_queues.amqp_notify_id();


--
-- Name: gk_moves before_00_updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_00_updated_on_datetime BEFORE UPDATE ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_moves_comments before_00_updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_00_updated_on_datetime BEFORE UPDATE ON geokrety.gk_moves_comments FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: scripts before_10_manage_ack; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_10_manage_ack BEFORE INSERT OR UPDATE OF locked_on_datetime, acked_on_datetime ON geokrety.scripts FOR EACH ROW EXECUTE FUNCTION geokrety.scripts_manage_ack();


--
-- Name: gk_users before_10_manage_email; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_10_manage_email BEFORE INSERT OR UPDATE OF _email_crypt, _email, _email_hash ON geokrety.gk_users FOR EACH ROW EXECUTE FUNCTION geokrety.manage_email();


--
-- Name: gk_geokrety before_10_manage_gkid; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_10_manage_gkid BEFORE INSERT OR UPDATE OF gkid ON geokrety.gk_geokrety FOR EACH ROW EXECUTE FUNCTION geokrety.geokret_gkid();


--
-- Name: gk_waypoints_gc before_10_manage_position; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_10_manage_position BEFORE INSERT OR UPDATE OF "position", lat, lon ON geokrety.gk_waypoints_gc FOR EACH ROW EXECUTE FUNCTION geokrety.moves_gis_updates();


--
-- Name: gk_waypoints_oc before_10_manage_position; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_10_manage_position BEFORE INSERT OR UPDATE OF lat, lon, "position" ON geokrety.gk_waypoints_oc FOR EACH ROW EXECUTE FUNCTION geokrety.moves_gis_updates();


--
-- Name: gk_account_activation before_10_manage_token; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_10_manage_token BEFORE INSERT OR UPDATE OF token ON geokrety.gk_account_activation FOR EACH ROW EXECUTE FUNCTION geokrety.account_activation_token_generate();


--
-- Name: gk_mails before_10_manage_token; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_10_manage_token BEFORE INSERT OR UPDATE OF token ON geokrety.gk_mails FOR EACH ROW EXECUTE FUNCTION geokrety.mails_token_generate();


--
-- Name: gk_owner_codes before_10_manage_token; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_10_manage_token BEFORE INSERT OR UPDATE OF token ON geokrety.gk_owner_codes FOR EACH ROW EXECUTE FUNCTION geokrety.owner_code_token_generate();


--
-- Name: gk_password_tokens before_10_manage_token; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_10_manage_token BEFORE INSERT OR UPDATE OF token ON geokrety.gk_password_tokens FOR EACH ROW EXECUTE FUNCTION geokrety.password_token_generate();


--
-- Name: gk_email_activation before_10_manage_tokens; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_10_manage_tokens BEFORE INSERT OR UPDATE OF token, revert_token ON geokrety.gk_email_activation FOR EACH ROW EXECUTE FUNCTION geokrety.email_activation_token_generate();


--
-- Name: gk_email_revalidate before_10_manage_tokens; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_10_manage_tokens BEFORE INSERT OR UPDATE OF token ON geokrety.gk_email_revalidate FOR EACH ROW EXECUTE FUNCTION geokrety.account_activation_token_generate();


--
-- Name: gk_moves before_10_moved_on_datetime_updater; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_10_moved_on_datetime_updater BEFORE INSERT ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.moves_moved_on_datetime_updater();


--
-- Name: gk_account_activation before_10_only_one_at_a_time; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_10_only_one_at_a_time BEFORE INSERT OR UPDATE OF used ON geokrety.gk_account_activation FOR EACH ROW EXECUTE FUNCTION geokrety.account_activation_check_only_one_active_per_user();


--
-- Name: gk_pictures before_10_picture_type_checker; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_10_picture_type_checker BEFORE INSERT OR UPDATE OF move, geokret, "user" ON geokrety.gk_pictures FOR EACH ROW EXECUTE FUNCTION geokrety.pictures_type_updater();


--
-- Name: sessions before_10_set_on_behalf; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_10_set_on_behalf BEFORE INSERT ON geokrety.sessions FOR EACH ROW EXECUTE FUNCTION geokrety.session_on_behalf_random();


--
-- Name: gk_moves_comments before_10_update_geokret; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_10_update_geokret BEFORE INSERT OR UPDATE OF move, geokret ON geokrety.gk_moves_comments FOR EACH ROW EXECUTE FUNCTION geokrety.moves_comments_manage_geokret();


--
-- Name: gk_moves_comments before_20_check_move_type_and_missing; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_20_check_move_type_and_missing BEFORE INSERT OR UPDATE OF move, geokret, type ON geokrety.gk_moves_comments FOR EACH ROW EXECUTE FUNCTION geokrety.moves_comments_missing_only_on_last_position();


--
-- Name: gk_moves before_20_gis_updates; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_20_gis_updates BEFORE INSERT OR UPDATE OF lat, lon, "position" ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.moves_gis_updates();


--
-- Name: gk_email_activation before_20_manage_email; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_20_manage_email BEFORE INSERT OR UPDATE OF _email, _email_crypt, _email_hash ON geokrety.gk_email_activation FOR EACH ROW EXECUTE FUNCTION geokrety.manage_email();


--
-- Name: gk_email_revalidate before_20_manage_email; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_20_manage_email BEFORE INSERT OR UPDATE OF _email, _email_hash, _email_crypt ON geokrety.gk_email_revalidate FOR EACH ROW EXECUTE FUNCTION geokrety.manage_email();


--
-- Name: gk_email_activation before_20_manage_previous_email; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_20_manage_previous_email BEFORE INSERT OR UPDATE OF _previous_email, _previous_email_crypt, _previous_email_hash ON geokrety.gk_email_activation FOR EACH ROW EXECUTE FUNCTION geokrety.manage_previous_email();


--
-- Name: gk_users before_20_manage_secid; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_20_manage_secid BEFORE INSERT OR UPDATE OF _secid_crypt, _secid, _secid_hash ON geokrety.gk_users FOR EACH ROW EXECUTE FUNCTION geokrety.user_secid_generate();


--
-- Name: gk_geokrety before_20_manage_tracking_code; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_20_manage_tracking_code BEFORE INSERT OR UPDATE OF tracking_code ON geokrety.gk_geokrety FOR EACH ROW EXECUTE FUNCTION geokrety.geokret_tracking_code();


--
-- Name: gk_owner_codes before_20_only_one_at_a_time; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_20_only_one_at_a_time BEFORE INSERT OR UPDATE OF geokret, used ON geokrety.gk_owner_codes FOR EACH ROW EXECUTE FUNCTION geokrety.owner_code_check_only_one_active_per_geokret();


--
-- Name: gk_geokrety before_30_manage_holder; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_30_manage_holder BEFORE INSERT OR UPDATE OF holder ON geokrety.gk_geokrety FOR EACH ROW EXECUTE FUNCTION geokrety.geokret_manage_holder();


--
-- Name: gk_email_revalidate before_30_manage_ip_validated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_30_manage_ip_validated_on_datetime BEFORE INSERT OR UPDATE OF used ON geokrety.gk_email_revalidate FOR EACH ROW EXECUTE FUNCTION geokrety.email_revalidate_validated_on_datetime_updater();


--
-- Name: gk_email_activation before_30_only_one_at_a_time; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_30_only_one_at_a_time BEFORE INSERT OR UPDATE OF "user", used, _email_hash ON geokrety.gk_email_activation FOR EACH ROW WHEN ((new.used = 0)) EXECUTE FUNCTION geokrety.email_activation_check_only_one_active_per_user();


--
-- Name: gk_users before_30_username_trim_spaces; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_30_username_trim_spaces BEFORE INSERT OR UPDATE OF username ON geokrety.gk_users FOR EACH ROW EXECUTE FUNCTION geokrety.user_trim_spaces();


--
-- Name: gk_moves before_30_waypoint_uppercase; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_30_waypoint_uppercase BEFORE INSERT OR UPDATE OF waypoint ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.moves_waypoint_uppercase();


--
-- Name: gk_email_activation before_40_check_email_used; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_40_check_email_used BEFORE INSERT OR UPDATE OF "user", used, _email_hash ON geokrety.gk_email_activation FOR EACH ROW WHEN ((new.used = 0)) EXECUTE FUNCTION geokrety.email_activation_check_email_already_used();


--
-- Name: gk_geokrety before_40_manage_birth_date; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_40_manage_birth_date BEFORE INSERT OR UPDATE OF born_on_datetime ON geokrety.gk_geokrety FOR EACH ROW EXECUTE FUNCTION geokrety.geokret_manage_birth_date();


--
-- Name: gk_email_revalidate before_40_only_one_at_a_time; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_40_only_one_at_a_time BEFORE INSERT ON geokrety.gk_email_revalidate FOR EACH ROW WHEN ((new.used = 0)) EXECUTE FUNCTION geokrety.email_revalidate_check_only_one_active_per_user();


--
-- Name: gk_moves before_40_update_missing; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_40_update_missing BEFORE INSERT OR UPDATE OF geokret, moved_on_datetime ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.moves_moved_on_datetime_checker();


--
-- Name: gk_users before_40_username_email_uniq; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_40_username_email_uniq BEFORE INSERT OR UPDATE OF username ON geokrety.gk_users FOR EACH ROW EXECUTE FUNCTION geokrety.user_username_as_email_not_taken();


--
-- Name: gk_moves before_50_check_archive_author; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_50_check_archive_author BEFORE INSERT OR UPDATE OF geokret, move_type ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.moves_check_archive_author();


--
-- Name: gk_users before_50_manage_home_position; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_50_manage_home_position BEFORE INSERT OR UPDATE OF home_latitude, home_longitude, home_position, home_country ON geokrety.gk_users FOR EACH ROW EXECUTE FUNCTION geokrety.user_manage_home_position();


--
-- Name: gk_email_revalidate before_50_update_user_account_status; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_50_update_user_account_status BEFORE UPDATE OF used ON geokrety.gk_email_revalidate FOR EACH ROW WHEN ((new.used = 1)) EXECUTE FUNCTION geokrety.email_revalidate_validated_update_user();


--
-- Name: gk_users before_60_manage_observation_area; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_60_manage_observation_area BEFORE INSERT OR UPDATE OF observation_area ON geokrety.gk_users FOR EACH ROW EXECUTE FUNCTION geokrety.user_manage_observation_area();


--
-- Name: gk_users before_70_set_username_deleted; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER before_70_set_username_deleted BEFORE DELETE ON geokrety.gk_users FOR EACH ROW EXECUTE FUNCTION geokrety.user_delete_anonymize();


--
-- Name: gk_news comments_count_override; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER comments_count_override AFTER UPDATE OF comments_count ON geokrety.gk_news FOR EACH ROW EXECUTE FUNCTION geokrety.news_comments_counts_override();


--
-- Name: gk_news_comments update_news; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER update_news AFTER INSERT OR DELETE OR UPDATE OF news ON geokrety.gk_news_comments FOR EACH ROW EXECUTE FUNCTION geokrety.news_comments_count_on_news_update();


--
-- Name: gk_account_activation updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_account_activation FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_awards updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_awards FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_awards_won updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_awards_won FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_email_activation updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_email_activation FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_email_revalidate updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_email_revalidate FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_geokrety updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_geokrety FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_geokrety_rating updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_geokrety_rating FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_labels updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_labels FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_news_comments updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_news_comments FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_password_tokens updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_password_tokens FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_pictures updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_pictures FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_races updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_races FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_races_participants updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_races_participants FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_site_settings updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_site_settings FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_site_settings_parameters updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_site_settings_parameters FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_social_auth_providers updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_social_auth_providers FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_users updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_users FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_users_authentication_history updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_users_authentication_history FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_users_settings updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_users_settings FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_users_settings_parameters updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_users_settings_parameters FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_users_social_auth updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_users_social_auth FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_users_username_history updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_users_username_history FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_watched updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_watched FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_waypoints_oc updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_waypoints_oc FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_waypoints_sync updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_waypoints_sync FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_yearly_ranking updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: -
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_yearly_ranking FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_account_activation gk_account_activation_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_account_activation
    ADD CONSTRAINT gk_account_activation_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- Name: gk_awards gk_awards_group_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_awards
    ADD CONSTRAINT gk_awards_group_fkey FOREIGN KEY ("group") REFERENCES geokrety.gk_awards_group(id) ON DELETE CASCADE;


--
-- Name: gk_awards_won gk_badges_award_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_awards_won
    ADD CONSTRAINT gk_badges_award_fkey FOREIGN KEY (award) REFERENCES geokrety.gk_awards(id) ON DELETE CASCADE;


--
-- Name: gk_awards_won gk_badges_holder_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_awards_won
    ADD CONSTRAINT gk_badges_holder_fkey FOREIGN KEY (holder) REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- Name: gk_email_activation gk_email_activation_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_email_activation
    ADD CONSTRAINT gk_email_activation_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- Name: gk_email_revalidate gk_email_revalidate_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_email_revalidate
    ADD CONSTRAINT gk_email_revalidate_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- Name: gk_geokrety gk_geokrety_avatar_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT gk_geokrety_avatar_fkey FOREIGN KEY (avatar) REFERENCES geokrety.gk_pictures(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: gk_geokrety gk_geokrety_holder_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT gk_geokrety_holder_fkey FOREIGN KEY (holder) REFERENCES geokrety.gk_users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: gk_geokrety gk_geokrety_label_template_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT gk_geokrety_label_template_fkey FOREIGN KEY (label_template) REFERENCES geokrety.gk_labels(id) ON DELETE SET NULL;


--
-- Name: gk_geokrety gk_geokrety_last_log_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT gk_geokrety_last_log_fkey FOREIGN KEY (last_log) REFERENCES geokrety.gk_moves(id) ON DELETE SET NULL;


--
-- Name: gk_geokrety gk_geokrety_last_position_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT gk_geokrety_last_position_fkey FOREIGN KEY (last_position) REFERENCES geokrety.gk_moves(id) ON DELETE SET NULL;


--
-- Name: gk_geokrety gk_geokrety_owner_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT gk_geokrety_owner_fkey FOREIGN KEY (owner) REFERENCES geokrety.gk_users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: gk_geokrety_rating gk_geokrety_rating_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_geokrety_rating
    ADD CONSTRAINT gk_geokrety_rating_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- Name: gk_geokrety_rating gk_geokrety_rating_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_geokrety_rating
    ADD CONSTRAINT gk_geokrety_rating_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE CASCADE;


--
-- Name: gk_mails gk_mails_from_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_mails
    ADD CONSTRAINT gk_mails_from_user_fkey FOREIGN KEY (from_user) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_mails gk_mails_to_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_mails
    ADD CONSTRAINT gk_mails_to_user_fkey FOREIGN KEY (to_user) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_moves gk_moves_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_moves
    ADD CONSTRAINT gk_moves_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: gk_moves_comments gk_moves_comments_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_moves_comments
    ADD CONSTRAINT gk_moves_comments_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_moves_comments gk_moves_comments_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_moves_comments
    ADD CONSTRAINT gk_moves_comments_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE CASCADE;


--
-- Name: gk_moves_comments gk_moves_comments_move_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_moves_comments
    ADD CONSTRAINT gk_moves_comments_move_fkey FOREIGN KEY (move) REFERENCES geokrety.gk_moves(id) ON DELETE CASCADE;


--
-- Name: gk_moves gk_moves_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_moves
    ADD CONSTRAINT gk_moves_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: gk_news gk_news_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_news
    ADD CONSTRAINT gk_news_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_news_comments_access gk_news_comments_access_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_news_comments_access
    ADD CONSTRAINT gk_news_comments_access_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- Name: gk_news_comments_access gk_news_comments_access_news_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_news_comments_access
    ADD CONSTRAINT gk_news_comments_access_news_fkey FOREIGN KEY (news) REFERENCES geokrety.gk_news(id) ON DELETE CASCADE;


--
-- Name: gk_news_comments gk_news_comments_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_news_comments
    ADD CONSTRAINT gk_news_comments_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_news_comments gk_news_comments_news_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_news_comments
    ADD CONSTRAINT gk_news_comments_news_fkey FOREIGN KEY (news) REFERENCES geokrety.gk_news(id) ON DELETE CASCADE;


--
-- Name: gk_owner_codes gk_owner_codes_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_owner_codes
    ADD CONSTRAINT gk_owner_codes_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE CASCADE;


--
-- Name: gk_owner_codes gk_owner_codes_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_owner_codes
    ADD CONSTRAINT gk_owner_codes_user_fkey FOREIGN KEY (adopter) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_password_tokens gk_password_tokens_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_password_tokens
    ADD CONSTRAINT gk_password_tokens_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- Name: gk_pictures gk_pictures_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_pictures
    ADD CONSTRAINT gk_pictures_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: gk_pictures gk_pictures_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_pictures
    ADD CONSTRAINT gk_pictures_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: gk_pictures gk_pictures_move_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_pictures
    ADD CONSTRAINT gk_pictures_move_fkey FOREIGN KEY (move) REFERENCES geokrety.gk_moves(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: gk_pictures gk_pictures_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_pictures
    ADD CONSTRAINT gk_pictures_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: gk_races gk_races_organizer_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_races
    ADD CONSTRAINT gk_races_organizer_fkey FOREIGN KEY (organizer) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_races_participants gk_races_participants_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_races_participants
    ADD CONSTRAINT gk_races_participants_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE CASCADE;


--
-- Name: gk_races_participants gk_races_participants_race_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_races_participants
    ADD CONSTRAINT gk_races_participants_race_fkey FOREIGN KEY (race) REFERENCES geokrety.gk_races(id) ON DELETE CASCADE;


--
-- Name: gk_site_settings gk_site_settings_name_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_site_settings
    ADD CONSTRAINT gk_site_settings_name_fkey FOREIGN KEY (name) REFERENCES geokrety.gk_site_settings_parameters(name) ON DELETE CASCADE;


--
-- Name: gk_users_authentication_history gk_users_authentication_history_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_users_authentication_history
    ADD CONSTRAINT gk_users_authentication_history_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- Name: gk_users gk_users_avatar_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_users
    ADD CONSTRAINT gk_users_avatar_fkey FOREIGN KEY (avatar) REFERENCES geokrety.gk_pictures(id) ON DELETE SET NULL;


--
-- Name: gk_users_settings gk_users_settings_name_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_users_settings
    ADD CONSTRAINT gk_users_settings_name_fkey FOREIGN KEY (name) REFERENCES geokrety.gk_users_settings_parameters(name) ON DELETE CASCADE;


--
-- Name: gk_users_settings gk_users_settings_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_users_settings
    ADD CONSTRAINT gk_users_settings_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- Name: gk_users_social_auth gk_users_social_auth_provider_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_users_social_auth
    ADD CONSTRAINT gk_users_social_auth_provider_fkey FOREIGN KEY (provider) REFERENCES geokrety.gk_social_auth_providers(id) ON DELETE CASCADE;


--
-- Name: gk_users_social_auth gk_users_social_auth_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_users_social_auth
    ADD CONSTRAINT gk_users_social_auth_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- Name: gk_users_username_history gk_users_username_history_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_users_username_history
    ADD CONSTRAINT gk_users_username_history_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- Name: gk_watched gk_watched_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_watched
    ADD CONSTRAINT gk_watched_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE CASCADE;


--
-- Name: gk_watched gk_watched_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_watched
    ADD CONSTRAINT gk_watched_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- Name: gk_yearly_ranking gk_yearly_ranking_award_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_yearly_ranking
    ADD CONSTRAINT gk_yearly_ranking_award_fkey FOREIGN KEY (award) REFERENCES geokrety.gk_awards_won(id) ON DELETE CASCADE;


--
-- Name: gk_yearly_ranking gk_yearly_ranking_group_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_yearly_ranking
    ADD CONSTRAINT gk_yearly_ranking_group_fkey FOREIGN KEY ("group") REFERENCES geokrety.gk_awards_group(id) ON DELETE CASCADE;


--
-- Name: gk_yearly_ranking gk_yearly_ranking_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: -
--

ALTER TABLE ONLY geokrety.gk_yearly_ranking
    ADD CONSTRAINT gk_yearly_ranking_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

