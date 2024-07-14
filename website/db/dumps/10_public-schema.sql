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
-- Name: public; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA IF NOT EXISTS public;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: countries; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.countries (
    id integer NOT NULL,
    iso_a2 character varying(2) NOT NULL,
    geom public.geometry(Geometry,4326) NOT NULL
);


--
-- Name: gk_countries_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.gk_countries_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gk_countries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.gk_countries_id_seq OWNED BY public.countries.id;


--
-- Name: srtm; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.srtm (
    rid integer NOT NULL,
    rast public.raster,
    filename text
);


--
-- Name: srtm_metadata; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.srtm_metadata AS
 SELECT rid,
    (md).upperleftx AS upperleftx,
    (md).upperlefty AS upperlefty,
    (md).width AS width,
    (md).height AS height,
    (md).scalex AS scalex,
    (md).scaley AS scaley,
    (md).skewx AS skewx,
    (md).skewy AS skewy,
    (md).srid AS srid,
    (md).numbands AS numbands
   FROM ( SELECT srtm.rid,
            public.st_metadata(srtm.rast) AS md
           FROM public.srtm) foo;


--
-- Name: srtm_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.srtm_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: srtm_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.srtm_rid_seq OWNED BY public.srtm.rid;


--
-- Name: timezones; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.timezones (
    gid integer NOT NULL,
    tzid character varying(80),
    geom public.geometry(MultiPolygon,4326)
);


--
-- Name: timezones_gid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.timezones_gid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: timezones_gid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.timezones_gid_seq OWNED BY public.timezones.gid;


--
-- Name: countries id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.countries ALTER COLUMN id SET DEFAULT nextval('public.gk_countries_id_seq'::regclass);


--
-- Name: srtm rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.srtm ALTER COLUMN rid SET DEFAULT nextval('public.srtm_rid_seq'::regclass);


--
-- Name: timezones gid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.timezones ALTER COLUMN gid SET DEFAULT nextval('public.timezones_gid_seq'::regclass);


--
-- Name: srtm srtm_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.srtm
    ADD CONSTRAINT srtm_pkey PRIMARY KEY (rid);


--
-- Name: timezones timezones_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.timezones
    ADD CONSTRAINT timezones_pkey PRIMARY KEY (gid);


--
-- Name: countries_index_geom; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX countries_index_geom ON public.countries USING gist (geom);


--
-- Name: gk_countries_iso_a2; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX gk_countries_iso_a2 ON public.countries USING btree (iso_a2);


--
-- Name: srtm_st_convexhull_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX srtm_st_convexhull_idx ON public.srtm USING gist (public.st_convexhull(rast));


--
-- Name: srtm_st_convexhull_idx1; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX srtm_st_convexhull_idx1 ON public.srtm USING gist (public.st_convexhull(rast));


--
-- Name: timezones_geom_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX timezones_geom_idx ON public.timezones USING gist (geom);


--
-- PostgreSQL database dump complete
--

