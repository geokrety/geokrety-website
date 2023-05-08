--
-- PostgreSQL database dump
--

-- Dumped from database version 12.2 (Debian 12.2-2.pgdg100+1)
-- Dumped by pg_dump version 12.2 (Ubuntu 12.2-4)

-- Started on 2020-08-03 13:48:12 CEST

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
-- TOC entry 10 (class 2615 OID 2200)
-- Name: public; Type: SCHEMA; Schema: -; Owner: geokrety
--

CREATE SCHEMA IF NOT EXISTS public;


--
-- TOC entry 5711 (class 0 OID 0)
-- Dependencies: 10
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: geokrety
--

COMMENT ON SCHEMA public IS 'standard public schema';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 232 (class 1259 OID 17989)
-- Name: countries; Type: TABLE; Schema: public; Owner: geokrety
--

CREATE TABLE public.countries (
    id integer NOT NULL,
    iso_a2 character varying(2) NOT NULL,
    geom public.geometry(Geometry,4326) NOT NULL
);


--
-- TOC entry 233 (class 1259 OID 17995)
-- Name: gk_countries_id_seq; Type: SEQUENCE; Schema: public; Owner: geokrety
--

CREATE SEQUENCE public.gk_countries_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 5712 (class 0 OID 0)
-- Dependencies: 233
-- Name: gk_countries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: geokrety
--

ALTER SEQUENCE public.gk_countries_id_seq OWNED BY public.countries.id;


--
-- TOC entry 234 (class 1259 OID 17997)
-- Name: srtm; Type: TABLE; Schema: public; Owner: geokrety
--

CREATE TABLE public.srtm (
    rid integer NOT NULL,
    rast public.raster,
    filename text
);


--
-- TOC entry 235 (class 1259 OID 18003)
-- Name: srtm_metadata; Type: VIEW; Schema: public; Owner: geokrety
--

CREATE VIEW public.srtm_metadata AS
 SELECT foo.rid,
    (foo.md).upperleftx AS upperleftx,
    (foo.md).upperlefty AS upperlefty,
    (foo.md).width AS width,
    (foo.md).height AS height,
    (foo.md).scalex AS scalex,
    (foo.md).scaley AS scaley,
    (foo.md).skewx AS skewx,
    (foo.md).skewy AS skewy,
    (foo.md).srid AS srid,
    (foo.md).numbands AS numbands
   FROM ( SELECT srtm.rid,
            public.st_metadata(srtm.rast) AS md
           FROM public.srtm) foo;


--
-- TOC entry 236 (class 1259 OID 18007)
-- Name: srtm_rid_seq; Type: SEQUENCE; Schema: public; Owner: geokrety
--

CREATE SEQUENCE public.srtm_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 5713 (class 0 OID 0)
-- Dependencies: 236
-- Name: srtm_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: geokrety
--

ALTER SEQUENCE public.srtm_rid_seq OWNED BY public.srtm.rid;


--
-- TOC entry 241 (class 1259 OID 510530)
-- Name: timezones; Type: TABLE; Schema: public; Owner: geokrety
--

CREATE TABLE public.timezones (
    gid integer NOT NULL,
    tzid character varying(80),
    geom public.geometry(MultiPolygon,4326)
);


--
-- TOC entry 240 (class 1259 OID 510528)
-- Name: timezones_gid_seq; Type: SEQUENCE; Schema: public; Owner: geokrety
--

CREATE SEQUENCE public.timezones_gid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 5714 (class 0 OID 0)
-- Dependencies: 240
-- Name: timezones_gid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: geokrety
--

ALTER SEQUENCE public.timezones_gid_seq OWNED BY public.timezones.gid;


--
-- TOC entry 5555 (class 2604 OID 18009)
-- Name: countries id; Type: DEFAULT; Schema: public; Owner: geokrety
--

ALTER TABLE ONLY public.countries ALTER COLUMN id SET DEFAULT nextval('public.gk_countries_id_seq'::regclass);


--
-- TOC entry 5556 (class 2604 OID 18010)
-- Name: srtm rid; Type: DEFAULT; Schema: public; Owner: geokrety
--

ALTER TABLE ONLY public.srtm ALTER COLUMN rid SET DEFAULT nextval('public.srtm_rid_seq'::regclass);


--
-- TOC entry 5557 (class 2604 OID 510533)
-- Name: timezones gid; Type: DEFAULT; Schema: public; Owner: geokrety
--

ALTER TABLE ONLY public.timezones ALTER COLUMN gid SET DEFAULT nextval('public.timezones_gid_seq'::regclass);


--
-- TOC entry 5561 (class 2606 OID 18012)
-- Name: srtm srtm_pkey; Type: CONSTRAINT; Schema: public; Owner: geokrety
--

ALTER TABLE ONLY public.srtm
    ADD CONSTRAINT srtm_pkey PRIMARY KEY (rid);


--
-- TOC entry 5566 (class 2606 OID 510535)
-- Name: timezones timezones_pkey; Type: CONSTRAINT; Schema: public; Owner: geokrety
--

ALTER TABLE ONLY public.timezones
    ADD CONSTRAINT timezones_pkey PRIMARY KEY (gid);


--
-- TOC entry 5558 (class 1259 OID 459472)
-- Name: countries_index_geom; Type: INDEX; Schema: public; Owner: geokrety
--

CREATE INDEX countries_index_geom ON public.countries USING gist (geom);


--
-- TOC entry 5559 (class 1259 OID 18013)
-- Name: gk_countries_iso_a2; Type: INDEX; Schema: public; Owner: geokrety
--

CREATE INDEX gk_countries_iso_a2 ON public.countries USING btree (iso_a2);


--
-- TOC entry 5562 (class 1259 OID 18014)
-- Name: srtm_st_convexhull_idx; Type: INDEX; Schema: public; Owner: geokrety
--

CREATE INDEX srtm_st_convexhull_idx ON public.srtm USING gist (public.st_convexhull(rast));


--
-- TOC entry 5563 (class 1259 OID 18015)
-- Name: srtm_st_convexhull_idx1; Type: INDEX; Schema: public; Owner: geokrety
--

CREATE INDEX srtm_st_convexhull_idx1 ON public.srtm USING gist (public.st_convexhull(rast));


--
-- TOC entry 5564 (class 1259 OID 510905)
-- Name: timezones_geom_idx; Type: INDEX; Schema: public; Owner: geokrety
--

CREATE INDEX timezones_geom_idx ON public.timezones USING gist (geom);


-- Completed on 2020-08-03 13:48:13 CEST

--
-- PostgreSQL database dump complete
--
