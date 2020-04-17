--
-- PostgreSQL database dump
--

-- Dumped from database version 12.2 (Debian 12.2-2.pgdg100+1)
-- Dumped by pg_dump version 12.2 (Ubuntu 12.2-2.pgdg19.10+1)

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
-- Name: geokrety; Type: SCHEMA; Schema: -; Owner: geokrety
--

CREATE SCHEMA geokrety;


ALTER SCHEMA geokrety OWNER TO geokrety;

--
-- Name: account_activation_check_validating_ip(inet, smallint); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.account_activation_check_validating_ip(validating_ip inet, used smallint) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN

IF used = ANY ('{0,2}'::smallint[]) AND validating_ip IS NULL THEN
	RETURN TRUE;
ELSIF used = 1::smallint AND validating_ip IS NOT NULL THEN
	RETURN TRUE;
END IF;

RETURN FALSE;
END;$$;


ALTER FUNCTION geokrety.account_activation_check_validating_ip(validating_ip inet, used smallint) OWNER TO geokrety;

--
-- Name: account_activation_token_generate(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.account_activation_token_generate() OWNER TO geokrety;

--
-- Name: coords2position(double precision, double precision, integer); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.coords2position(lat double precision, lon double precision, OUT "position" public.geography, srid integer DEFAULT 4326) RETURNS public.geography
    LANGUAGE sql
    AS $$SELECT public.ST_SetSRID(public.ST_MakePoint(lon, lat), srid)::public.geography as position;$$;


ALTER FUNCTION geokrety.coords2position(lat double precision, lon double precision, OUT "position" public.geography, srid integer) OWNER TO geokrety;

--
-- Name: email_activation_check_used_ip_datetime(smallint, inet, timestamp with time zone, inet, timestamp with time zone); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.email_activation_check_used_ip_datetime(used smallint, updating_ip inet, used_on_datetime timestamp with time zone, reverting_ip inet, reverted_on_datetime timestamp with time zone) OWNER TO geokrety;

--
-- Name: email_activation_token_generate(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.email_activation_token_generate() OWNER TO geokrety;

--
-- Name: fresher_than(timestamp with time zone, integer, character varying); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.fresher_than(datetime timestamp with time zone, duration integer, unit character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN
	RETURN datetime > NOW() - CAST(duration || ' ' || unit as INTERVAL);
END;$$;


ALTER FUNCTION geokrety.fresher_than(datetime timestamp with time zone, duration integer, unit character varying) OWNER TO geokrety;

--
-- Name: generate_adoption_token(integer); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.generate_adoption_token(size integer DEFAULT 5) RETURNS character varying
    LANGUAGE sql
    AS $$SELECT array_to_string(array(select substr('0123456789',((random()*(10-1)+1)::integer),1) from generate_series(1,size)),'');$$;


ALTER FUNCTION geokrety.generate_adoption_token(size integer) OWNER TO geokrety;

--
-- Name: generate_password_token(integer); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.generate_password_token(size integer DEFAULT 42) RETURNS character varying
    LANGUAGE sql
    AS $$SELECT array_to_string(array(select substr('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz',((random()*(62-1)+1)::integer),1) from generate_series(1,size)),'');$$;


ALTER FUNCTION geokrety.generate_password_token(size integer) OWNER TO geokrety;

--
-- Name: generate_secid(integer); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.generate_secid(size integer DEFAULT 42) RETURNS character varying
    LANGUAGE sql
    AS $$SELECT array_to_string(array(select substr('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz',((random()*(62-1)+1)::integer),1) from generate_series(1,size)),'');$$;


ALTER FUNCTION geokrety.generate_secid(size integer) OWNER TO geokrety;

--
-- Name: generate_tracking_code(integer); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.generate_tracking_code(size integer DEFAULT 6) RETURNS character varying
    LANGUAGE sql
    AS $$SELECT array_to_string(array(select substr('ABCDEFGHJKMNPQRSTUVWXYZ23456789',((random()*(31-1)+1)::integer),1) from generate_series(1,size)),'');$$;


ALTER FUNCTION geokrety.generate_tracking_code(size integer) OWNER TO geokrety;

--
-- Name: generate_verification_token(integer); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.generate_verification_token(size integer DEFAULT 42) RETURNS character varying
    LANGUAGE sql
    AS $$SELECT array_to_string(array(select substr('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz',((random()*(62-1)+1)::integer),1) from generate_series(1,size)),'');$$;


ALTER FUNCTION geokrety.generate_verification_token(size integer) OWNER TO geokrety;

--
-- Name: geokret_compute_missing(bigint); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.geokret_compute_missing(lastposition_id bigint) RETURNS boolean
    LANGUAGE sql
    AS $$SELECT COUNT(*) > 0
FROM "gk_moves_comments"
WHERE "move" = lastposition_id
AND "type" = 1;
$$;


ALTER FUNCTION geokrety.geokret_compute_missing(lastposition_id bigint) OWNER TO geokrety;

--
-- Name: geokret_compute_total_distance(bigint); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.geokret_compute_total_distance(geokret_id bigint) OWNER TO geokrety;

--
-- Name: geokret_compute_total_places_visited(bigint); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.geokret_compute_total_places_visited(geokret_id bigint) OWNER TO geokrety;

--
-- Name: geokret_gkid(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.geokret_gkid() OWNER TO geokrety;

--
-- Name: geokret_manage_holder(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.geokret_manage_holder() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

IF NEW.holder IS NULL THEN
	NEW.holder = NEW.owner;
	RETURN NEW;
END IF;

RETURN NEW;
END;$$;


ALTER FUNCTION geokrety.geokret_manage_holder() OWNER TO geokrety;

--
-- Name: geokret_tracking_code(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.geokret_tracking_code() RETURNS trigger
    LANGUAGE plpgsql
    AS $$DECLARE
found_tc bool;
BEGIN

IF (NEW.tracking_code IS NOT NULL AND LENGTH(NEW.tracking_code) >= 6) THEN
	NEW.tracking_code = UPPER(NEW.tracking_code);
	RETURN NEW;
END IF;

LOOP
	NEW.tracking_code = generate_tracking_code();
	SELECT COUNT(*) = 0 FROM gk_geokrety WHERE tracking_code = NEW.tracking_code INTO found_tc;
	EXIT WHEN found_tc;
END LOOP;

RETURN NEW;
END;$$;


ALTER FUNCTION geokrety.geokret_tracking_code() OWNER TO geokrety;

--
-- Name: geokrety_compute_last_log(bigint); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.geokrety_compute_last_log(geokret_id bigint) RETURNS bigint
    LANGUAGE sql
    AS $$SELECT id
FROM "gk_moves"
WHERE geokret = geokret_id
ORDER BY moved_on_datetime DESC
LIMIT 1;$$;


ALTER FUNCTION geokrety.geokrety_compute_last_log(geokret_id bigint) OWNER TO geokrety;

--
-- Name: geokrety_compute_last_log_and_last_position(bigint); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.geokrety_compute_last_log_and_last_position(geokret_id bigint) OWNER TO geokrety;

--
-- Name: geokrety_compute_last_position(bigint); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.geokrety_compute_last_position(geokret_id bigint) RETURNS bigint
    LANGUAGE sql
    AS $$SELECT id
FROM "gk_moves"
WHERE geokret = geokret_id
AND move_type = ANY (moves_type_last_position())
ORDER BY moved_on_datetime DESC
LIMIT 1;$$;


ALTER FUNCTION geokrety.geokrety_compute_last_position(geokret_id bigint) OWNER TO geokrety;

--
-- Name: mails_token_generate(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.mails_token_generate() OWNER TO geokrety;

--
-- Name: move_counting_kilometers(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.move_counting_kilometers() RETURNS smallint[]
    LANGUAGE sql
    AS $$SELECT '{0,3,5}'::smallint[]$$;


ALTER FUNCTION geokrety.move_counting_kilometers() OWNER TO geokrety;

--
-- Name: move_or_moves_comments_manage_geokret_missing(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.move_or_moves_comments_manage_geokret_missing() OWNER TO geokrety;

--
-- Name: move_requiring_coordinates(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.move_requiring_coordinates() RETURNS smallint[]
    LANGUAGE sql
    AS $$SELECT '{0,3,5}'::smallint[]$$;


ALTER FUNCTION geokrety.move_requiring_coordinates() OWNER TO geokrety;

--
-- Name: move_type_count_kilometers(smallint); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.move_type_count_kilometers(move_type smallint) OWNER TO geokrety;

--
-- Name: move_type_require_coordinates(smallint); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.move_type_require_coordinates(move_type smallint) OWNER TO geokrety;

--
-- Name: moves_check_author_username(bigint, character varying); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.moves_check_author_username(author_id bigint, username character varying) OWNER TO geokrety;

--
-- Name: moves_check_waypoint(smallint, character varying); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.moves_check_waypoint(move_type smallint, waypoint character varying) OWNER TO geokrety;

--
-- Name: moves_comments_count_on_move_update(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.moves_comments_count_on_move_update() OWNER TO geokrety;

--
-- Name: moves_comments_manage_geokret(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.moves_comments_manage_geokret() OWNER TO geokrety;

--
-- Name: moves_comments_missing_only_on_last_position(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.moves_comments_missing_only_on_last_position() OWNER TO geokrety;

--
-- Name: moves_count_comments(bigint); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.moves_count_comments(move_id bigint) OWNER TO geokrety;

--
-- Name: moves_distances_after(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.moves_distances_after() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

-- Move that in before_update
--IF (TG_OP = 'DELETE') THEN
--	PERFORM update_next_move_distance(OLD.geokret, OLD.id, true);
--	RETURN OLD;
--END IF;

IF (TG_OP = 'INSERT' OR TG_OP = 'UPDATE') THEN
	IF (OLD.geokret != NEW.geokret) THEN
		-- Updating old position
		PERFORM update_next_move_distance(OLD.geokret, OLD.id, true);
		PERFORM geokret_compute_total_places_visited(OLD.geokret);
		PERFORM geokret_compute_total_distance(OLD.geokret);
	END IF;
	PERFORM update_next_move_distance(NEW.geokret, NEW.id);
END IF;

PERFORM geokret_compute_total_distance(NEW.geokret);
PERFORM geokret_compute_total_places_visited(NEW.geokret);

RETURN NEW;
END;$$;


ALTER FUNCTION geokrety.moves_distances_after() OWNER TO geokrety;

--
-- Name: moves_get_on_page(bigint, bigint, bigint); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.moves_get_on_page(move_id bigint, per_page bigint, geokret_id bigint) OWNER TO geokrety;

--
-- Name: moves_gis_updates(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.moves_gis_updates() RETURNS trigger
    LANGUAGE plpgsql
    AS $$DECLARE
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
	SELECT iso_a2
	FROM public.countries
	WHERE public.ST_Intersects(geog, NEW.position::public.geography)
	INTO country;

	SELECT public.ST_Value(rast, NEW.position::public.geometry) As elevation
	FROM public.srtm
	WHERE public.ST_Intersects(rast, NEW.position::public.geometry)
	INTO elevation;

	NEW.country := LOWER(country);
	NEW.elevation := elevation;
END IF;

RETURN NEW;
END;$$;


ALTER FUNCTION geokrety.moves_gis_updates() OWNER TO geokrety;

--
-- Name: moves_log_type_and_position(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.moves_log_type_and_position() OWNER TO geokrety;

--
-- Name: moves_moved_on_datetime_checker(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.moves_moved_on_datetime_checker() RETURNS trigger
    LANGUAGE plpgsql
    AS $$DECLARE
_geokret gk_geokrety;
BEGIN

SELECT *
FROM gk_geokrety
WHERE id = NEW.geokret
INTO _geokret;

-- move before GK birth
IF DATE_TRUNC('MINUTE', NEW.moved_on_datetime) < DATE_TRUNC('MINUTE', _geokret.created_on_datetime) THEN
	RAISE 'Move date (%) time can not be before GeoKret birth (%)', DATE_TRUNC('MINUTE', NEW.moved_on_datetime), DATE_TRUNC('MINUTE', _geokret.created_on_datetime);
-- move after NOW()
ELSIF NEW.moved_on_datetime > NOW()::timestamp(0) THEN
	RAISE 'The date is in the future (if you are an inventor of a time travelling machine, contact us please!)';
-- same move on this GK at this datetime
ELSIF COUNT(*) > 0 FROM gk_moves WHERE moved_on_datetime = NEW.moved_on_datetime AND "geokret" = NEW.geokret AND id != NEW.id THEN
	RAISE 'A move at the exact same date already exists for this GeoKret';
END IF;

RETURN NEW;
END;$$;


ALTER FUNCTION geokrety.moves_moved_on_datetime_checker() OWNER TO geokrety;

--
-- Name: moves_moved_on_datetime_updater(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.moves_moved_on_datetime_updater() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN
	IF (TG_OP = 'INSERT' AND NEW.moved_on_datetime is NULL) THEN
		NEW.moved_on_datetime = NEW.created_on_datetime;
	END IF;
	
	RETURN NEW;
END;$$;


ALTER FUNCTION geokrety.moves_moved_on_datetime_updater() OWNER TO geokrety;

--
-- Name: moves_type_change(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.moves_type_change() OWNER TO geokrety;

--
-- Name: moves_type_last_position(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.moves_type_last_position() RETURNS smallint[]
    LANGUAGE sql
    AS $$SELECT '{0,1,3,4,5}'::smallint[]$$;


ALTER FUNCTION geokrety.moves_type_last_position() OWNER TO geokrety;

--
-- Name: moves_type_waypoint(smallint, character varying); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.moves_type_waypoint(move_type smallint, waypoint character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN

IF NOT(move_type = ANY (geokrety.move_requiring_coordinates())) AND waypoint IS NOT NULL THEN
	RAISE 'waypoint must be null when move_type is %', "move_type";
END IF;

RETURN TRUE;
END;$$;


ALTER FUNCTION geokrety.moves_type_waypoint(move_type smallint, waypoint character varying) OWNER TO geokrety;

--
-- Name: moves_types_markable_as_missing(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.moves_types_markable_as_missing() RETURNS smallint[]
    LANGUAGE sql
    AS $$SELECT '{0,3}'::smallint[]$$;


ALTER FUNCTION geokrety.moves_types_markable_as_missing() OWNER TO geokrety;

--
-- Name: moves_waypoint_uppercase(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.moves_waypoint_uppercase() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN
	NEW.waypoint = UPPER(NEW.waypoint);
	RETURN NEW;
END;$$;


ALTER FUNCTION geokrety.moves_waypoint_uppercase() OWNER TO geokrety;

--
-- Name: news_comments_count_on_news_update(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.news_comments_count_on_news_update() OWNER TO geokrety;

--
-- Name: news_comments_counts_override(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.news_comments_counts_override() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

PERFORM news_compute_news_comments_count(NEW.id);

RETURN NEW;
END;$$;


ALTER FUNCTION geokrety.news_comments_counts_override() OWNER TO geokrety;

--
-- Name: news_compute_news_comments_count(bigint); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.news_compute_news_comments_count(news_id bigint) OWNER TO geokrety;

--
-- Name: on_update_current_timestamp(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.on_update_current_timestamp() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN NEW.updated_on_datetime = now(); RETURN NEW; END;$$;


ALTER FUNCTION geokrety.on_update_current_timestamp() OWNER TO geokrety;

--
-- Name: owner_code_check_only_one_active_per_geokret(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.owner_code_check_only_one_active_per_geokret() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

PERFORM owner_code_check_only_one_active_per_geokret(NEW.geokret);

RETURN NEW;
END;$$;


ALTER FUNCTION geokrety.owner_code_check_only_one_active_per_geokret() OWNER TO geokrety;

--
-- Name: owner_code_check_only_one_active_per_geokret(bigint); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.owner_code_check_only_one_active_per_geokret(geokret_id bigint) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN

IF COUNT(*) > 1 FROM "gk_owner_codes" WHERE geokret = geokret_id AND id IS NOT NULL AND used = 0 THEN
	RAISE 'An owner code for this GeoKret already exists';
END IF;

RETURN TRUE;
END;$$;


ALTER FUNCTION geokrety.owner_code_check_only_one_active_per_geokret(geokret_id bigint) OWNER TO geokrety;

--
-- Name: owner_code_check_validating_ip(inet, smallint, timestamp with time zone, bigint); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.owner_code_check_validating_ip(validating_ip inet, used smallint, claimed_on_datetime timestamp with time zone, "user" bigint) OWNER TO geokrety;

--
-- Name: owner_code_token_generate(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.owner_code_token_generate() OWNER TO geokrety;

--
-- Name: password_check_validating_ip(inet, smallint, timestamp with time zone); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.password_check_validating_ip(validating_ip inet, used smallint, used_on_datetime timestamp with time zone) OWNER TO geokrety;

--
-- Name: password_token_generate(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.password_token_generate() OWNER TO geokrety;

--
-- Name: picture_type_to_table_name(bigint, bigint, bigint); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.picture_type_to_table_name(geokret bigint, move bigint, "user" bigint, OUT table_name character varying, OUT id bigint, OUT type smallint) OWNER TO geokrety;

--
-- Name: pictures_counter(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.pictures_counter() OWNER TO geokrety;

--
-- Name: pictures_type_updater(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.pictures_type_updater() OWNER TO geokrety;

--
-- Name: position2coords(public.geography, integer); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.position2coords("position" public.geography, OUT lat double precision, OUT lon double precision, srid integer DEFAULT 4326) RETURNS record
    LANGUAGE sql
    AS $$SELECT public.ST_Y(position::public.geometry) as lat,
       public.ST_X(position::public.geometry) as lon;$$;


ALTER FUNCTION geokrety.position2coords("position" public.geography, OUT lat double precision, OUT lon double precision, srid integer) OWNER TO geokrety;

--
-- Name: random_between(integer, integer); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.random_between(low integer, high integer) RETURNS integer
    LANGUAGE plpgsql STRICT
    AS $$BEGIN
	RETURN floor(random()* (high-low + 1) + low);
END;$$;


ALTER FUNCTION geokrety.random_between(low integer, high integer) OWNER TO geokrety;

--
-- Name: save_gc_waypoints(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.save_gc_waypoints() OWNER TO geokrety;

--
-- Name: update_next_move_distance(bigint, bigint, boolean); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.update_next_move_distance(geokret_id bigint, move_id bigint, exclude_current boolean) OWNER TO geokrety;

--
-- Name: user_secid_generate(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.user_secid_generate() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN

IF (NEW.secid IS NOT NULL) THEN
	RETURN NEW;
END IF;

NEW.secid = generate_secid();

RETURN NEW;
END;$$;


ALTER FUNCTION geokrety.user_secid_generate() OWNER TO geokrety;

--
-- Name: valid_move_types(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.valid_move_types() RETURNS smallint[]
    LANGUAGE sql
    AS $$SELECT '{0,1,2,3,4,5}'::smallint[]$$;


ALTER FUNCTION geokrety.valid_move_types() OWNER TO geokrety;

--
-- Name: valid_moves_comments_types(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.valid_moves_comments_types() RETURNS smallint[]
    LANGUAGE sql
    AS $$SELECT '{0,1}'::smallint[]$$;


ALTER FUNCTION geokrety.valid_moves_comments_types() OWNER TO geokrety;

--
-- Name: validate_move_types(smallint); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.validate_move_types(move_type smallint) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN
	RETURN move_type = ANY (valid_move_types());
END;$$;


ALTER FUNCTION geokrety.validate_move_types(move_type smallint) OWNER TO geokrety;

--
-- Name: validate_moves_comments_missing(smallint); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.validate_moves_comments_missing(move_type smallint) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN

IF (NOT (move_type = ANY (moves_types_markable_as_missing()))) THEN
	RAISE '`missing` status cannot be set for such move type';
END IF;

RETURN TRUE;
END;$$;


ALTER FUNCTION geokrety.validate_moves_comments_missing(move_type smallint) OWNER TO geokrety;

--
-- Name: validate_moves_comments_type(smallint); Type: FUNCTION; Schema: geokrety; Owner: geokrety
--

CREATE FUNCTION geokrety.validate_moves_comments_type(comment_type smallint) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN
	RETURN comment_type = ANY (valid_moves_comments_types());
END;$$;


ALTER FUNCTION geokrety.validate_moves_comments_type(comment_type smallint) OWNER TO geokrety;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
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
-- Name: COLUMN gk_pictures.type; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_pictures.type IS 'const PICTURE_GEOKRET_AVATAR = 0; const PICTURE_GEOKRET_MOVE = 1; const PICTURE_USER_AVATAR = 2;';


--
-- Name: validate_picture_type_against_parameters(geokrety.gk_pictures); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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
	RAISE 'Move type unrecognized (%)', row_p.type USING ERRCODE = 'data_exception';
ELSIF (row_p.geokret IS NULL AND row_p.move IS NULL AND row_p.user IS NULL) THEN
	RAISE 'One of Geokret (%), Move (%) or User (%) must be specified', row_p.geokret, row_p.move, row_p.user USING ERRCODE = 'data_exception';
END IF;

RAISE 'Move `type` does not match the specified arguments.' USING ERRCODE = 'data_exception';

END;$$;


ALTER FUNCTION geokrety.validate_picture_type_against_parameters(row_p geokrety.gk_pictures) OWNER TO geokrety;

--
-- Name: waypoints_gc_fill_from_moves(); Type: FUNCTION; Schema: geokrety; Owner: geokrety
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


ALTER FUNCTION geokrety.waypoints_gc_fill_from_moves() OWNER TO geokrety;

--
-- Name: gk_account_activation; Type: TABLE; Schema: geokrety; Owner: geokrety
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
    CONSTRAINT check_validating_ip CHECK (geokrety.account_activation_check_validating_ip(validating_ip, used)),
    CONSTRAINT validate_used CHECK ((used = ANY (ARRAY[0, 1, 2])))
);


ALTER TABLE geokrety.gk_account_activation OWNER TO geokrety;

--
-- Name: COLUMN gk_account_activation.used; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_account_activation.used IS '0=unused 1=validated 2=expired';


--
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
-- Name: account_activation_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.account_activation_id_seq OWNED BY geokrety.gk_account_activation.id;


--
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
-- Name: badges_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.badges_id_seq OWNED BY geokrety.gk_badges.id;


--
-- Name: gk_email_activation; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_email_activation (
    id bigint NOT NULL,
    token character varying(60),
    revert_token character varying(60),
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
    CONSTRAINT validate_used_ip_datetime CHECK (geokrety.email_activation_check_used_ip_datetime(used, updating_ip, used_on_datetime, reverting_ip, reverted_on_datetime)),
    CONSTRAINT validate_used_value CHECK ((used = ANY (ARRAY[0, 1, 2, 3, 4, 5, 6])))
);


ALTER TABLE geokrety.gk_email_activation OWNER TO geokrety;

--
-- Name: COLUMN gk_email_activation.previous_email; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_email_activation.previous_email IS 'Store the previous in case of needed rollback';


--
-- Name: COLUMN gk_email_activation.used; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_email_activation.used IS 'TOKEN_UNUSED = 0
TOKEN_CHANGED = 1
TOKEN_REFUSED = 2
TOKEN_EXPIRED = 3
TOKEN_DISABLED = 4
TOKEN_VALIDATED = 5
TOKEN_REVERTED = 6';


--
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
-- Name: email_activation_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.email_activation_id_seq OWNED BY geokrety.gk_email_activation.id;


--
-- Name: gk_geokrety; Type: TABLE; Schema: geokrety; Owner: geokrety
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
    CONSTRAINT validate_type CHECK ((type = ANY (ARRAY[0, 1, 2, 3, 4])))
);


ALTER TABLE geokrety.gk_geokrety OWNER TO geokrety;

--
-- Name: COLUMN gk_geokrety.gkid; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_geokrety.gkid IS 'The real GK id : https://stackoverflow.com/a/33791018/944936';


--
-- Name: COLUMN gk_geokrety.holder; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_geokrety.holder IS 'In the hands of user';


--
-- Name: COLUMN gk_geokrety.missing; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_geokrety.missing IS 'true=missing';


--
-- Name: COLUMN gk_geokrety.type; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_geokrety.type IS '0, 1, 2, 3, 4';


--
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
-- Name: geokrety_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.geokrety_id_seq OWNED BY geokrety.gk_geokrety.id;


--
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
-- Name: COLUMN gk_geokrety_rating.rate; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_geokrety_rating.rate IS 'single rating (number of stars)';


--
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
-- Name: geokrety_rating_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.geokrety_rating_id_seq OWNED BY geokrety.gk_geokrety_rating.id;


--
-- Name: gk_audit_logs; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_audit_logs (
    log_datetime timestamp with time zone DEFAULT now() NOT NULL,
    event character varying,
    author bigint,
    ip inet,
    context json
);


ALTER TABLE geokrety.gk_audit_logs OWNER TO geokrety;

--
-- Name: gk_mails; Type: TABLE; Schema: geokrety; Owner: geokrety
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


ALTER TABLE geokrety.gk_mails OWNER TO geokrety;

--
-- Name: gk_moves; Type: TABLE; Schema: geokrety; Owner: geokrety
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


ALTER TABLE geokrety.gk_moves OWNER TO geokrety;

--
-- Name: COLUMN gk_moves.elevation; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_moves.elevation IS '-32768 when alt cannot be found';


--
-- Name: COLUMN gk_moves.country; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_moves.country IS 'ISO 3166-1 https://fr.wikipedia.org/wiki/ISO_3166-1';


--
-- Name: COLUMN gk_moves.app; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_moves.app IS 'source of the log';


--
-- Name: COLUMN gk_moves.app_ver; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_moves.app_ver IS 'application version/codename';


--
-- Name: COLUMN gk_moves.moved_on_datetime; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_moves.moved_on_datetime IS 'The move as configured by user';


--
-- Name: COLUMN gk_moves.move_type; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_moves.move_type IS '0=drop, 1=grab, 2=comment, 3=met, 4=arch, 5=dip';


--
-- Name: gk_moves_comments; Type: TABLE; Schema: geokrety; Owner: geokrety
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


ALTER TABLE geokrety.gk_moves_comments OWNER TO geokrety;

--
-- Name: COLUMN gk_moves_comments.type; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_moves_comments.type IS '0=comment, 1=missing';


--
-- Name: gk_news; Type: TABLE; Schema: geokrety; Owner: geokrety
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


ALTER TABLE geokrety.gk_news OWNER TO geokrety;

--
-- Name: gk_news_comments; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_news_comments (
    id bigint NOT NULL,
    news bigint NOT NULL,
    author bigint,
    content character varying(1000) NOT NULL,
    created_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE geokrety.gk_news_comments OWNER TO geokrety;

--
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
-- Name: gk_owner_codes; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_owner_codes (
    id bigint NOT NULL,
    geokret bigint NOT NULL,
    token character varying(20),
    generated_on_datetime timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    claimed_on_datetime timestamp(0) with time zone,
    adopter bigint,
    validating_ip inet,
    used smallint DEFAULT '0'::smallint NOT NULL,
    CONSTRAINT check_validating_ip CHECK (geokrety.owner_code_check_validating_ip(validating_ip, used, claimed_on_datetime, adopter))
);


ALTER TABLE geokrety.gk_owner_codes OWNER TO geokrety;

--
-- Name: COLUMN gk_owner_codes.used; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_owner_codes.used IS '0=unused 1=used';


--
-- Name: gk_password_tokens; Type: TABLE; Schema: geokrety; Owner: geokrety
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


ALTER TABLE geokrety.gk_password_tokens OWNER TO geokrety;

--
-- Name: COLUMN gk_password_tokens.used; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_password_tokens.used IS '0=unused 1=used';


--
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
    CONSTRAINT validate_type CHECK (((type)::text = ANY (ARRAY[('wpt'::character varying)::text, ('targetDistance'::character varying)::text, ('targetCaches'::character varying)::text, ('maxDistance'::character varying)::text, ('maxCaches'::character varying)::text])))
);


ALTER TABLE geokrety.gk_races OWNER TO geokrety;

--
-- Name: COLUMN gk_races.created_on_datetime; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_races.created_on_datetime IS 'Creation date';


--
-- Name: COLUMN gk_races.private; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_races.private IS '0 = public, 1 = private';


--
-- Name: COLUMN gk_races.password; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_races.password IS 'password to join the race';


--
-- Name: COLUMN gk_races.start_on_datetime; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_races.start_on_datetime IS 'Race start date';


--
-- Name: COLUMN gk_races.end_on_datetime; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_races.end_on_datetime IS 'Race end date';


--
-- Name: COLUMN gk_races.target_dist; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_races.target_dist IS 'target distance';


--
-- Name: COLUMN gk_races.target_caches; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_races.target_caches IS 'targeted number of caches';


--
-- Name: COLUMN gk_races.status; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_races.status IS 'race status. 0 = initialized, 1 = persist, 2 = finite, 3 = finished but logs flow down';


--
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
-- Name: gk_statistics_counters; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_statistics_counters (
    id integer NOT NULL,
    name character varying(32) NOT NULL,
    value double precision NOT NULL
);


ALTER TABLE geokrety.gk_statistics_counters OWNER TO geokrety;

--
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
-- Name: gk_statistics_counters_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.gk_statistics_counters_id_seq OWNED BY geokrety.gk_statistics_counters.id;


--
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
-- Name: gk_statistics_daily_counters_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.gk_statistics_daily_counters_id_seq OWNED BY geokrety.gk_statistics_daily_counters.id;


--
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
    daily_mails_hour smallint DEFAULT geokrety.random_between(0, 23) NOT NULL,
    avatar bigint,
    pictures_count integer DEFAULT 0 NOT NULL,
    last_mail_datetime timestamp(0) with time zone,
    last_login_datetime timestamp(0) with time zone,
    terms_of_use_datetime timestamp(0) with time zone,
    secid character varying(128),
    statpic_template integer DEFAULT 1 NOT NULL,
    email_invalid smallint DEFAULT '0'::smallint NOT NULL,
    account_valid smallint DEFAULT '0'::smallint NOT NULL,
    CONSTRAINT validate_account_valid CHECK ((account_valid = ANY (ARRAY[0, 1]))),
    CONSTRAINT validate_email_invalid CHECK ((email_invalid = ANY (ARRAY[0, 1, 2])))
);


ALTER TABLE geokrety.gk_users OWNER TO geokrety;

--
-- Name: COLUMN gk_users.pictures_count; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_users.pictures_count IS 'Attached avatar count';


--
-- Name: COLUMN gk_users.terms_of_use_datetime; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_users.terms_of_use_datetime IS 'Acceptation date';


--
-- Name: COLUMN gk_users.secid; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_users.secid IS 'connect by other applications';


--
-- Name: COLUMN gk_users.account_valid; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_users.account_valid IS '0=unconfirmed 1=confirmed';


--
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
-- Name: gk_waypoints_country; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_waypoints_country (
    original character varying(191) NOT NULL,
    country character varying(191)
);


ALTER TABLE geokrety.gk_waypoints_country OWNER TO geokrety;

--
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
-- Name: gk_waypoints_gc; Type: TABLE; Schema: geokrety; Owner: geokrety
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


ALTER TABLE geokrety.gk_waypoints_gc OWNER TO geokrety;

--
-- Name: gk_waypoints_oc; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_waypoints_oc (
    id bigint NOT NULL,
    waypoint character varying(11) NOT NULL,
    lat double precision,
    lon double precision,
    elevation integer DEFAULT '-32768'::integer NOT NULL,
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


ALTER TABLE geokrety.gk_waypoints_oc OWNER TO geokrety;

--
-- Name: COLUMN gk_waypoints_oc.country; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_waypoints_oc.country IS 'country code as ISO 3166-1 alpha-2';


--
-- Name: COLUMN gk_waypoints_oc.country_name; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_waypoints_oc.country_name IS 'full English country name';


--
-- Name: COLUMN gk_waypoints_oc.status; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON COLUMN geokrety.gk_waypoints_oc.status IS '0, 1, 2, 3, 6, 7';


--
-- Name: gk_waypoints_sync; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_waypoints_sync (
    service_id character varying(5) NOT NULL,
    last_update character varying(15)
);


ALTER TABLE geokrety.gk_waypoints_sync OWNER TO geokrety;

--
-- Name: TABLE gk_waypoints_sync; Type: COMMENT; Schema: geokrety; Owner: geokrety
--

COMMENT ON TABLE geokrety.gk_waypoints_sync IS 'Last synchronization time for GC services';


--
-- Name: gk_waypoints_types; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.gk_waypoints_types (
    type character varying(255) NOT NULL,
    cache_type character varying(255)
);


ALTER TABLE geokrety.gk_waypoints_types OWNER TO geokrety;

--
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
-- Name: mails_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.mails_id_seq OWNED BY geokrety.gk_mails.id;


--
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
-- Name: move_comments_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.move_comments_id_seq OWNED BY geokrety.gk_moves_comments.id;


--
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
-- Name: moves_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.moves_id_seq OWNED BY geokrety.gk_moves.id;


--
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
-- Name: news_comments_access_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.news_comments_access_id_seq OWNED BY geokrety.gk_news_comments_access.id;


--
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
-- Name: news_comments_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.news_comments_id_seq OWNED BY geokrety.gk_news_comments.id;


--
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
-- Name: news_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.news_id_seq OWNED BY geokrety.gk_news.id;


--
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
-- Name: owner_codes_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.owner_codes_id_seq OWNED BY geokrety.gk_owner_codes.id;


--
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
-- Name: password_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.password_tokens_id_seq OWNED BY geokrety.gk_password_tokens.id;


--
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
-- Name: pictures_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.pictures_id_seq OWNED BY geokrety.gk_pictures.id;


--
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
-- Name: races_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.races_id_seq OWNED BY geokrety.gk_races.id;


--
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
-- Name: races_participants_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.races_participants_id_seq OWNED BY geokrety.gk_races_participants.id;


--
-- Name: scripts; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.scripts (
    id bigint NOT NULL,
    name character varying(128) NOT NULL,
    last_run_datetime timestamp with time zone
);


ALTER TABLE geokrety.scripts OWNER TO geokrety;

--
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
-- Name: scripts_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.scripts_id_seq OWNED BY geokrety.scripts.id;


--
-- Name: sessions; Type: TABLE; Schema: geokrety; Owner: geokrety
--

CREATE TABLE geokrety.sessions (
    session_id character varying(255) NOT NULL,
    data text,
    ip character varying(45),
    agent character varying(300),
    stamp integer
);


ALTER TABLE geokrety.sessions OWNER TO geokrety;

--
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
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.users_id_seq OWNED BY geokrety.gk_users.id;


--
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
-- Name: watched_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.watched_id_seq OWNED BY geokrety.gk_watched.id;


--
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
-- Name: waypoints_id_seq; Type: SEQUENCE OWNED BY; Schema: geokrety; Owner: geokrety
--

ALTER SEQUENCE geokrety.waypoints_id_seq OWNED BY geokrety.gk_waypoints_oc.id;


--
-- Name: gk_account_activation id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_account_activation ALTER COLUMN id SET DEFAULT nextval('geokrety.account_activation_id_seq'::regclass);


--
-- Name: gk_badges id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_badges ALTER COLUMN id SET DEFAULT nextval('geokrety.badges_id_seq'::regclass);


--
-- Name: gk_email_activation id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_email_activation ALTER COLUMN id SET DEFAULT nextval('geokrety.email_activation_id_seq'::regclass);


--
-- Name: gk_geokrety id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety ALTER COLUMN id SET DEFAULT nextval('geokrety.geokrety_id_seq'::regclass);


--
-- Name: gk_geokrety_rating id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety_rating ALTER COLUMN id SET DEFAULT nextval('geokrety.geokrety_rating_id_seq'::regclass);


--
-- Name: gk_mails id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_mails ALTER COLUMN id SET DEFAULT nextval('geokrety.mails_id_seq'::regclass);


--
-- Name: gk_moves id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_moves ALTER COLUMN id SET DEFAULT nextval('geokrety.moves_id_seq'::regclass);


--
-- Name: gk_moves_comments id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_moves_comments ALTER COLUMN id SET DEFAULT nextval('geokrety.move_comments_id_seq'::regclass);


--
-- Name: gk_news id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news ALTER COLUMN id SET DEFAULT nextval('geokrety.news_id_seq'::regclass);


--
-- Name: gk_news_comments id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news_comments ALTER COLUMN id SET DEFAULT nextval('geokrety.news_comments_id_seq'::regclass);


--
-- Name: gk_news_comments_access id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news_comments_access ALTER COLUMN id SET DEFAULT nextval('geokrety.news_comments_access_id_seq'::regclass);


--
-- Name: gk_owner_codes id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_owner_codes ALTER COLUMN id SET DEFAULT nextval('geokrety.owner_codes_id_seq'::regclass);


--
-- Name: gk_password_tokens id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_password_tokens ALTER COLUMN id SET DEFAULT nextval('geokrety.password_tokens_id_seq'::regclass);


--
-- Name: gk_pictures id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_pictures ALTER COLUMN id SET DEFAULT nextval('geokrety.pictures_id_seq'::regclass);


--
-- Name: gk_races id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_races ALTER COLUMN id SET DEFAULT nextval('geokrety.races_id_seq'::regclass);


--
-- Name: gk_races_participants id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_races_participants ALTER COLUMN id SET DEFAULT nextval('geokrety.races_participants_id_seq'::regclass);


--
-- Name: gk_statistics_counters id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_statistics_counters ALTER COLUMN id SET DEFAULT nextval('geokrety.gk_statistics_counters_id_seq'::regclass);


--
-- Name: gk_statistics_daily_counters id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_statistics_daily_counters ALTER COLUMN id SET DEFAULT nextval('geokrety.gk_statistics_daily_counters_id_seq'::regclass);


--
-- Name: gk_users id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_users ALTER COLUMN id SET DEFAULT nextval('geokrety.users_id_seq'::regclass);


--
-- Name: gk_watched id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_watched ALTER COLUMN id SET DEFAULT nextval('geokrety.watched_id_seq'::regclass);


--
-- Name: gk_waypoints_oc id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_waypoints_oc ALTER COLUMN id SET DEFAULT nextval('geokrety.waypoints_id_seq'::regclass);


--
-- Name: scripts id; Type: DEFAULT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.scripts ALTER COLUMN id SET DEFAULT nextval('geokrety.scripts_id_seq'::regclass);


--
-- Name: gk_geokrety gk_geokrety_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT gk_geokrety_primary PRIMARY KEY (id);


--
-- Name: gk_mails gk_mails_token_uniq; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_mails
    ADD CONSTRAINT gk_mails_token_uniq UNIQUE (token);


--
-- Name: gk_news_comments_access gk_news_comments_access_news_author; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news_comments_access
    ADD CONSTRAINT gk_news_comments_access_news_author UNIQUE (news, author);


--
-- Name: gk_news_comments_access gk_news_comments_access_news_pkey; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news_comments_access
    ADD CONSTRAINT gk_news_comments_access_news_pkey PRIMARY KEY (id);


--
-- Name: gk_pictures gk_pictures_id; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_pictures
    ADD CONSTRAINT gk_pictures_id PRIMARY KEY (id);


--
-- Name: gk_statistics_counters gk_statistics_counters_id; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_statistics_counters
    ADD CONSTRAINT gk_statistics_counters_id PRIMARY KEY (id);


--
-- Name: gk_statistics_daily_counters gk_statistics_daily_counters_date; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_statistics_daily_counters
    ADD CONSTRAINT gk_statistics_daily_counters_date UNIQUE (date);


--
-- Name: gk_statistics_daily_counters gk_statistics_daily_counters_id; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_statistics_daily_counters
    ADD CONSTRAINT gk_statistics_daily_counters_id PRIMARY KEY (id);


--
-- Name: gk_waypoints_gc gk_waypoints_gc_id; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_waypoints_gc
    ADD CONSTRAINT gk_waypoints_gc_id PRIMARY KEY (id);


--
-- Name: gk_waypoints_gc gk_waypoints_gc_position; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_waypoints_gc
    ADD CONSTRAINT gk_waypoints_gc_position UNIQUE ("position");


--
-- Name: gk_waypoints_gc gk_waypoints_gc_waypoint; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_waypoints_gc
    ADD CONSTRAINT gk_waypoints_gc_waypoint UNIQUE (waypoint);


--
-- Name: gk_waypoints_sync gk_waypoints_sync_service_id; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_waypoints_sync
    ADD CONSTRAINT gk_waypoints_sync_service_id UNIQUE (service_id);


--
-- Name: gk_waypoints_types gk_waypoints_types_type; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_waypoints_types
    ADD CONSTRAINT gk_waypoints_types_type UNIQUE (type);


--
-- Name: gk_account_activation idx_20969_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_account_activation
    ADD CONSTRAINT idx_20969_primary PRIMARY KEY (id);


--
-- Name: gk_badges idx_20984_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_badges
    ADD CONSTRAINT idx_20984_primary PRIMARY KEY (id);


--
-- Name: gk_email_activation idx_20991_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_email_activation
    ADD CONSTRAINT idx_20991_primary PRIMARY KEY (id);


--
-- Name: gk_geokrety_rating idx_21016_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety_rating
    ADD CONSTRAINT idx_21016_primary PRIMARY KEY (id);


--
-- Name: gk_moves_comments idx_21034_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_moves_comments
    ADD CONSTRAINT idx_21034_primary PRIMARY KEY (id);


--
-- Name: gk_moves idx_21044_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_moves
    ADD CONSTRAINT idx_21044_primary PRIMARY KEY (id);


--
-- Name: gk_news idx_21058_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news
    ADD CONSTRAINT idx_21058_primary PRIMARY KEY (id);


--
-- Name: gk_news_comments idx_21069_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news_comments
    ADD CONSTRAINT idx_21069_primary PRIMARY KEY (id);


--
-- Name: gk_owner_codes idx_21085_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_owner_codes
    ADD CONSTRAINT idx_21085_primary PRIMARY KEY (id);


--
-- Name: gk_password_tokens idx_21092_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_password_tokens
    ADD CONSTRAINT idx_21092_primary PRIMARY KEY (id);


--
-- Name: gk_races idx_21114_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_races
    ADD CONSTRAINT idx_21114_primary PRIMARY KEY (id);


--
-- Name: gk_users idx_21135_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_users
    ADD CONSTRAINT idx_21135_primary PRIMARY KEY (id);


--
-- Name: gk_watched idx_21153_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_watched
    ADD CONSTRAINT idx_21153_primary PRIMARY KEY (id);


--
-- Name: gk_waypoints_oc idx_21160_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_waypoints_oc
    ADD CONSTRAINT idx_21160_primary PRIMARY KEY (id);


--
-- Name: phinxlog idx_21180_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.phinxlog
    ADD CONSTRAINT idx_21180_primary PRIMARY KEY (version);


--
-- Name: scripts idx_21189_primary; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.scripts
    ADD CONSTRAINT idx_21189_primary PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (session_id);


--
-- Name: audit_logs_index_event; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX audit_logs_index_event ON geokrety.gk_audit_logs USING btree (event);


--
-- Name: audit_logs_index_log_datetime; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX audit_logs_index_log_datetime ON geokrety.gk_audit_logs USING btree (log_datetime);


--
-- Name: gk_geokrety_uniq_tracking_code; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE UNIQUE INDEX gk_geokrety_uniq_tracking_code ON geokrety.gk_geokrety USING btree (tracking_code);


--
-- Name: gk_moves_country; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX gk_moves_country ON geokrety.gk_moves USING btree (country);


--
-- Name: gk_pictures_key; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX gk_pictures_key ON geokrety.gk_pictures USING btree (key);


--
-- Name: gk_users_uniq_secid; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE UNIQUE INDEX gk_users_uniq_secid ON geokrety.gk_users USING btree (secid);


--
-- Name: gk_users_uniq_username; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE UNIQUE INDEX gk_users_uniq_username ON geokrety.gk_users USING btree (username);


--
-- Name: gk_waypoints_waypoint; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX gk_waypoints_waypoint ON geokrety.gk_waypoints_oc USING btree (waypoint);


--
-- Name: idx_20969_user; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_20969_user ON geokrety.gk_account_activation USING btree ("user");


--
-- Name: idx_20984_timestamp; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_20984_timestamp ON geokrety.gk_badges USING btree (awarded_on_datetime);


--
-- Name: idx_20984_userid; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_20984_userid ON geokrety.gk_badges USING btree (holder);


--
-- Name: idx_20991_token; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_20991_token ON geokrety.gk_email_activation USING btree (token);


--
-- Name: idx_20991_user; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_20991_user ON geokrety.gk_email_activation USING btree ("user");


--
-- Name: idx_21002_avatarid; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21002_avatarid ON geokrety.gk_geokrety USING btree (avatar);


--
-- Name: idx_21002_hands_of_index; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21002_hands_of_index ON geokrety.gk_geokrety USING btree (holder);


--
-- Name: idx_21002_ost_log_id; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21002_ost_log_id ON geokrety.gk_geokrety USING btree (last_log);


--
-- Name: idx_21002_ost_pozycja_id; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21002_ost_pozycja_id ON geokrety.gk_geokrety USING btree (last_position);


--
-- Name: idx_21002_owner; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21002_owner ON geokrety.gk_geokrety USING btree (owner);


--
-- Name: idx_21016_geokret; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21016_geokret ON geokrety.gk_geokrety_rating USING btree (geokret);


--
-- Name: idx_21016_user; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21016_user ON geokrety.gk_geokrety_rating USING btree (author);


--
-- Name: idx_21024_from; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21024_from ON geokrety.gk_mails USING btree (from_user);


--
-- Name: idx_21024_id_maila; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE UNIQUE INDEX idx_21024_id_maila ON geokrety.gk_mails USING btree (id);


--
-- Name: idx_21024_to; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21024_to ON geokrety.gk_mails USING btree (to_user);


--
-- Name: idx_21034_kret_id; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21034_kret_id ON geokrety.gk_moves_comments USING btree (geokret);


--
-- Name: idx_21034_ruch_id; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21034_ruch_id ON geokrety.gk_moves_comments USING btree (move);


--
-- Name: idx_21034_user_id; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21034_user_id ON geokrety.gk_moves_comments USING btree (author);


--
-- Name: idx_21044_alt; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21044_alt ON geokrety.gk_moves USING btree (elevation);


--
-- Name: idx_21044_data; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21044_data ON geokrety.gk_moves USING btree (created_on_datetime);


--
-- Name: idx_21044_data_dodania; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21044_data_dodania ON geokrety.gk_moves USING btree (moved_on_datetime);


--
-- Name: idx_21044_id_2; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21044_id_2 ON geokrety.gk_moves USING btree (geokret);


--
-- Name: idx_21044_lat; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21044_lat ON geokrety.gk_moves USING btree (lat);


--
-- Name: idx_21044_lon; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21044_lon ON geokrety.gk_moves USING btree (lon);


--
-- Name: idx_21044_timestamp; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21044_timestamp ON geokrety.gk_moves USING btree (updated_on_datetime);


--
-- Name: idx_21044_user; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21044_user ON geokrety.gk_moves USING btree (author);


--
-- Name: idx_21044_waypoint; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21044_waypoint ON geokrety.gk_moves USING btree (waypoint);


--
-- Name: idx_21058_date; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21058_date ON geokrety.gk_news USING btree (created_on_datetime);


--
-- Name: idx_21058_userid; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21058_userid ON geokrety.gk_news USING btree (author);


--
-- Name: idx_21069_author; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21069_author ON geokrety.gk_news_comments USING btree (author);


--
-- Name: idx_21069_news; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21069_news ON geokrety.gk_news_comments USING btree (news);


--
-- Name: idx_21079_id; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE UNIQUE INDEX idx_21079_id ON geokrety.gk_news_comments_access USING btree (id);


--
-- Name: idx_21079_user; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21079_user ON geokrety.gk_news_comments_access USING btree (author);


--
-- Name: idx_21085_code; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21085_code ON geokrety.gk_owner_codes USING btree (token);


--
-- Name: idx_21085_kret_id; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21085_kret_id ON geokrety.gk_owner_codes USING btree (geokret);


--
-- Name: idx_21085_user; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21085_user ON geokrety.gk_owner_codes USING btree (adopter);


--
-- Name: idx_21092_created_on_datetime; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21092_created_on_datetime ON geokrety.gk_password_tokens USING btree (created_on_datetime);


--
-- Name: idx_21092_user; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21092_user ON geokrety.gk_password_tokens USING btree ("user");


--
-- Name: idx_21114_organizer; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21114_organizer ON geokrety.gk_races USING btree (organizer);


--
-- Name: idx_21125_geokret; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21125_geokret ON geokrety.gk_races_participants USING btree (geokret);


--
-- Name: idx_21125_race; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21125_race ON geokrety.gk_races_participants USING btree (race);


--
-- Name: idx_21125_racegkid; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE UNIQUE INDEX idx_21125_racegkid ON geokrety.gk_races_participants USING btree (id);


--
-- Name: idx_21135_avatar; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21135_avatar ON geokrety.gk_users USING btree (avatar);


--
-- Name: idx_21135_email; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21135_email ON geokrety.gk_users USING btree (email);


--
-- Name: idx_21135_ostatni_login; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21135_ostatni_login ON geokrety.gk_users USING btree (last_login_datetime);


--
-- Name: idx_21135_username; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21135_username ON geokrety.gk_users USING btree (username);


--
-- Name: idx_21135_username_email; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21135_username_email ON geokrety.gk_users USING btree (username, email);


--
-- Name: idx_21153_id; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21153_id ON geokrety.gk_watched USING btree (geokret);


--
-- Name: idx_21153_userid; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE INDEX idx_21153_userid ON geokrety.gk_watched USING btree ("user");


--
-- Name: idx_21171_unique_kraj; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE UNIQUE INDEX idx_21171_unique_kraj ON geokrety.gk_waypoints_country USING btree (original);


--
-- Name: idx_21189_name; Type: INDEX; Schema: geokrety; Owner: geokrety
--

CREATE UNIQUE INDEX idx_21189_name ON geokrety.scripts USING btree (name);


--
-- Name: gk_owner_codes after_10_check_only_one_active_per_geokret; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER after_10_check_only_one_active_per_geokret AFTER INSERT OR UPDATE OF geokret, used ON geokrety.gk_owner_codes FOR EACH ROW EXECUTE FUNCTION geokrety.owner_code_check_only_one_active_per_geokret();


--
-- Name: gk_pictures after_10_pictures_counter_updater; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER after_10_pictures_counter_updater AFTER INSERT OR DELETE OR UPDATE OF move, geokret, "user", uploaded_on_datetime ON geokrety.gk_pictures FOR EACH ROW EXECUTE FUNCTION geokrety.pictures_counter();


--
-- Name: gk_moves_comments after_10_update_missing; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER after_10_update_missing AFTER INSERT OR DELETE OR UPDATE OF geokret, type ON geokrety.gk_moves_comments FOR EACH ROW EXECUTE FUNCTION geokrety.move_or_moves_comments_manage_geokret_missing();


--
-- Name: gk_moves after_10_update_picture; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER after_10_update_picture AFTER UPDATE OF geokret ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.moves_type_change();


--
-- Name: gk_moves after_20_distances; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER after_20_distances AFTER INSERT OR DELETE OR UPDATE OF geokret, lat, lon, moved_on_datetime, move_type, "position" ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.moves_distances_after();


--
-- Name: gk_moves_comments after_20_updates_moves; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER after_20_updates_moves AFTER INSERT OR DELETE OR UPDATE OF move ON geokrety.gk_moves_comments FOR EACH ROW EXECUTE FUNCTION geokrety.moves_comments_count_on_move_update();


--
-- Name: gk_moves after_30_last_log_and_position; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER after_30_last_log_and_position AFTER INSERT OR DELETE OR UPDATE OF geokret, moved_on_datetime, move_type ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.moves_log_type_and_position();


--
-- Name: gk_moves after_40_update_missing; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER after_40_update_missing AFTER INSERT OR DELETE OR UPDATE OF geokret, moved_on_datetime, move_type ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.move_or_moves_comments_manage_geokret_missing();


--
-- Name: gk_moves after_50_manage_waypoint_gc; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER after_50_manage_waypoint_gc AFTER INSERT ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.save_gc_waypoints();


--
-- Name: gk_moves before_00_updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER before_00_updated_on_datetime BEFORE UPDATE ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_moves_comments before_00_updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER before_00_updated_on_datetime BEFORE UPDATE ON geokrety.gk_moves_comments FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_geokrety before_10_manage_gkid; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER before_10_manage_gkid BEFORE INSERT OR UPDATE OF gkid ON geokrety.gk_geokrety FOR EACH ROW EXECUTE FUNCTION geokrety.geokret_gkid();


--
-- Name: gk_waypoints_gc before_10_manage_position; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER before_10_manage_position BEFORE INSERT OR UPDATE OF "position", lat, lon ON geokrety.gk_waypoints_gc FOR EACH ROW EXECUTE FUNCTION geokrety.moves_gis_updates();


--
-- Name: gk_account_activation before_10_manage_token; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER before_10_manage_token BEFORE INSERT OR UPDATE OF token ON geokrety.gk_account_activation FOR EACH ROW EXECUTE FUNCTION geokrety.account_activation_token_generate();


--
-- Name: gk_mails before_10_manage_token; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER before_10_manage_token BEFORE INSERT OR UPDATE OF token ON geokrety.gk_mails FOR EACH ROW EXECUTE FUNCTION geokrety.mails_token_generate();


--
-- Name: gk_owner_codes before_10_manage_token; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER before_10_manage_token BEFORE INSERT OR UPDATE OF token ON geokrety.gk_owner_codes FOR EACH ROW EXECUTE FUNCTION geokrety.owner_code_token_generate();


--
-- Name: gk_password_tokens before_10_manage_token; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER before_10_manage_token BEFORE INSERT OR UPDATE OF token ON geokrety.gk_password_tokens FOR EACH ROW EXECUTE FUNCTION geokrety.password_token_generate();


--
-- Name: gk_email_activation before_10_manage_tokens; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER before_10_manage_tokens BEFORE INSERT OR UPDATE OF token, revert_token ON geokrety.gk_email_activation FOR EACH ROW EXECUTE FUNCTION geokrety.email_activation_token_generate();


--
-- Name: gk_moves before_10_moved_on_datetime_updater; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER before_10_moved_on_datetime_updater BEFORE INSERT ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.moves_moved_on_datetime_updater();


--
-- Name: gk_pictures before_10_picture_type_checker; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER before_10_picture_type_checker BEFORE INSERT OR UPDATE OF move, geokret, "user" ON geokrety.gk_pictures FOR EACH ROW EXECUTE FUNCTION geokrety.pictures_type_updater();


--
-- Name: gk_moves_comments before_10_update_geokret; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER before_10_update_geokret BEFORE INSERT OR UPDATE OF move, geokret ON geokrety.gk_moves_comments FOR EACH ROW EXECUTE FUNCTION geokrety.moves_comments_manage_geokret();


--
-- Name: gk_moves_comments before_20_check_move_type_and_missing; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER before_20_check_move_type_and_missing BEFORE INSERT OR UPDATE OF move, geokret, type ON geokrety.gk_moves_comments FOR EACH ROW EXECUTE FUNCTION geokrety.moves_comments_missing_only_on_last_position();


--
-- Name: gk_moves before_20_gis_updates; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER before_20_gis_updates BEFORE INSERT OR UPDATE OF lat, lon, "position" ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.moves_gis_updates();


--
-- Name: gk_geokrety before_20_manage_tracking_code; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER before_20_manage_tracking_code BEFORE INSERT OR UPDATE OF tracking_code ON geokrety.gk_geokrety FOR EACH ROW EXECUTE FUNCTION geokrety.geokret_tracking_code();


--
-- Name: gk_geokrety before_30_manage_holder; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER before_30_manage_holder BEFORE INSERT OR UPDATE OF holder ON geokrety.gk_geokrety FOR EACH ROW EXECUTE FUNCTION geokrety.geokret_manage_holder();


--
-- Name: gk_moves before_30_waypoint_uppercase; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER before_30_waypoint_uppercase BEFORE INSERT OR UPDATE OF waypoint ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.moves_waypoint_uppercase();


--
-- Name: gk_moves before_40_update_missing; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER before_40_update_missing BEFORE INSERT OR UPDATE OF geokret, moved_on_datetime ON geokrety.gk_moves FOR EACH ROW EXECUTE FUNCTION geokrety.moves_moved_on_datetime_checker();


--
-- Name: gk_news comments_count_override; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER comments_count_override AFTER UPDATE OF comments_count ON geokrety.gk_news FOR EACH ROW EXECUTE FUNCTION geokrety.news_comments_counts_override();


--
-- Name: gk_users manage_secid; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER manage_secid BEFORE INSERT OR UPDATE OF secid ON geokrety.gk_users FOR EACH ROW EXECUTE FUNCTION geokrety.user_secid_generate();


--
-- Name: gk_news_comments update_news; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER update_news AFTER INSERT OR DELETE OR UPDATE OF news ON geokrety.gk_news_comments FOR EACH ROW EXECUTE FUNCTION geokrety.news_comments_count_on_news_update();


--
-- Name: gk_account_activation updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_account_activation FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_badges updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_badges FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_email_activation updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_email_activation FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_geokrety updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_geokrety FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_geokrety_rating updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_geokrety_rating FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_news_comments updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_news_comments FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_password_tokens updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_password_tokens FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_pictures updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_pictures FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_races updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_races FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_races_participants updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_races_participants FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_users updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_users FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_watched updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_watched FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_waypoints_oc updated_on_datetime; Type: TRIGGER; Schema: geokrety; Owner: geokrety
--

CREATE TRIGGER updated_on_datetime BEFORE UPDATE ON geokrety.gk_waypoints_oc FOR EACH ROW EXECUTE FUNCTION geokrety.on_update_current_timestamp();


--
-- Name: gk_account_activation gk_account_activation_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_account_activation
    ADD CONSTRAINT gk_account_activation_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- Name: gk_badges gk_badges_holder_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_badges
    ADD CONSTRAINT gk_badges_holder_fkey FOREIGN KEY (holder) REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- Name: gk_email_activation gk_email_activation_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_email_activation
    ADD CONSTRAINT gk_email_activation_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- Name: gk_geokrety gk_geokrety_avatar_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT gk_geokrety_avatar_fkey FOREIGN KEY (avatar) REFERENCES geokrety.gk_pictures(id) ON DELETE SET NULL;


--
-- Name: gk_geokrety gk_geokrety_holder_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT gk_geokrety_holder_fkey FOREIGN KEY (holder) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_geokrety gk_geokrety_last_log_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT gk_geokrety_last_log_fkey FOREIGN KEY (last_log) REFERENCES geokrety.gk_moves(id) ON DELETE SET NULL;


--
-- Name: gk_geokrety gk_geokrety_last_position_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT gk_geokrety_last_position_fkey FOREIGN KEY (last_position) REFERENCES geokrety.gk_moves(id) ON DELETE SET NULL;


--
-- Name: gk_geokrety gk_geokrety_owner_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety
    ADD CONSTRAINT gk_geokrety_owner_fkey FOREIGN KEY (owner) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_geokrety_rating gk_geokrety_rating_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety_rating
    ADD CONSTRAINT gk_geokrety_rating_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- Name: gk_geokrety_rating gk_geokrety_rating_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_geokrety_rating
    ADD CONSTRAINT gk_geokrety_rating_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE CASCADE;


--
-- Name: gk_mails gk_mails_from_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_mails
    ADD CONSTRAINT gk_mails_from_user_fkey FOREIGN KEY (from_user) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_mails gk_mails_to_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_mails
    ADD CONSTRAINT gk_mails_to_user_fkey FOREIGN KEY (to_user) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_moves gk_moves_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_moves
    ADD CONSTRAINT gk_moves_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_moves_comments gk_moves_comments_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_moves_comments
    ADD CONSTRAINT gk_moves_comments_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_moves_comments gk_moves_comments_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_moves_comments
    ADD CONSTRAINT gk_moves_comments_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE CASCADE;


--
-- Name: gk_moves_comments gk_moves_comments_move_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_moves_comments
    ADD CONSTRAINT gk_moves_comments_move_fkey FOREIGN KEY (move) REFERENCES geokrety.gk_moves(id) ON DELETE CASCADE;


--
-- Name: gk_moves gk_moves_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_moves
    ADD CONSTRAINT gk_moves_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE CASCADE;


--
-- Name: gk_news gk_news_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news
    ADD CONSTRAINT gk_news_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_news_comments_access gk_news_comments_access_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news_comments_access
    ADD CONSTRAINT gk_news_comments_access_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- Name: gk_news_comments_access gk_news_comments_access_news_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news_comments_access
    ADD CONSTRAINT gk_news_comments_access_news_fkey FOREIGN KEY (news) REFERENCES geokrety.gk_news(id) ON DELETE CASCADE;


--
-- Name: gk_news_comments gk_news_comments_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news_comments
    ADD CONSTRAINT gk_news_comments_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_news_comments gk_news_comments_news_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_news_comments
    ADD CONSTRAINT gk_news_comments_news_fkey FOREIGN KEY (news) REFERENCES geokrety.gk_news(id) ON DELETE CASCADE;


--
-- Name: gk_owner_codes gk_owner_codes_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_owner_codes
    ADD CONSTRAINT gk_owner_codes_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE CASCADE;


--
-- Name: gk_owner_codes gk_owner_codes_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_owner_codes
    ADD CONSTRAINT gk_owner_codes_user_fkey FOREIGN KEY (adopter) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_password_tokens gk_password_tokens_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_password_tokens
    ADD CONSTRAINT gk_password_tokens_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- Name: gk_pictures gk_pictures_author_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_pictures
    ADD CONSTRAINT gk_pictures_author_fkey FOREIGN KEY (author) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_pictures gk_pictures_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_pictures
    ADD CONSTRAINT gk_pictures_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE SET NULL;


--
-- Name: gk_pictures gk_pictures_move_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_pictures
    ADD CONSTRAINT gk_pictures_move_fkey FOREIGN KEY (move) REFERENCES geokrety.gk_moves(id) ON DELETE SET NULL;


--
-- Name: gk_pictures gk_pictures_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_pictures
    ADD CONSTRAINT gk_pictures_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_races gk_races_organizer_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_races
    ADD CONSTRAINT gk_races_organizer_fkey FOREIGN KEY (organizer) REFERENCES geokrety.gk_users(id) ON DELETE SET NULL;


--
-- Name: gk_races_participants gk_races_participants_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_races_participants
    ADD CONSTRAINT gk_races_participants_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE CASCADE;


--
-- Name: gk_races_participants gk_races_participants_race_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_races_participants
    ADD CONSTRAINT gk_races_participants_race_fkey FOREIGN KEY (race) REFERENCES geokrety.gk_races(id) ON DELETE CASCADE;


--
-- Name: gk_users gk_users_avatar_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_users
    ADD CONSTRAINT gk_users_avatar_fkey FOREIGN KEY (avatar) REFERENCES geokrety.gk_pictures(id) ON DELETE SET NULL;


--
-- Name: gk_watched gk_watched_geokret_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_watched
    ADD CONSTRAINT gk_watched_geokret_fkey FOREIGN KEY (geokret) REFERENCES geokrety.gk_geokrety(id) ON DELETE CASCADE;


--
-- Name: gk_watched gk_watched_user_fkey; Type: FK CONSTRAINT; Schema: geokrety; Owner: geokrety
--

ALTER TABLE ONLY geokrety.gk_watched
    ADD CONSTRAINT gk_watched_user_fkey FOREIGN KEY ("user") REFERENCES geokrety.gk_users(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

