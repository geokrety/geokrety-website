--
-- PostgreSQL database dump
--

-- Dumped from database version 12.2 (Debian 12.2-2.pgdg100+1)
-- Dumped by pg_dump version 12.2 (Ubuntu 12.2-2.pgdg19.10+1)

-- Started on 2020-04-15 19:00:57 CEST

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

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 281 (class 1259 OID 498649)
-- Name: countries; Type: TABLE; Schema: public; Owner: geokrety
--

CREATE TABLE public.countries (
    id integer DEFAULT nextval('public.gk_countries_id_seq'::regclass) NOT NULL,
    iso_a2 character varying(2) NOT NULL,
    geog public.geography NOT NULL
);


ALTER TABLE public.countries OWNER TO geokrety;

--
-- TOC entry 225 (class 1259 OID 106059)
-- Name: srtm; Type: TABLE; Schema: public; Owner: geokrety
--

CREATE TABLE public.srtm (
    rid integer NOT NULL,
    rast public.raster,
    filename text
);


ALTER TABLE public.srtm OWNER TO geokrety;

--
-- TOC entry 226 (class 1259 OID 106065)
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


ALTER TABLE public.srtm_metadata OWNER TO geokrety;

--
-- TOC entry 208 (class 1259 OID 104455)
-- Name: srtm_rid_seq; Type: SEQUENCE; Schema: public; Owner: geokrety
--

CREATE SEQUENCE public.srtm_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.srtm_rid_seq OWNER TO geokrety;

--
-- TOC entry 5594 (class 0 OID 0)
-- Dependencies: 208
-- Name: srtm_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: geokrety
--

ALTER SEQUENCE public.srtm_rid_seq OWNED BY public.srtm.rid;


--
-- TOC entry 5444 (class 2604 OID 106070)
-- Name: srtm rid; Type: DEFAULT; Schema: public; Owner: geokrety
--

ALTER TABLE ONLY public.srtm ALTER COLUMN rid SET DEFAULT nextval('public.srtm_rid_seq'::regclass);


--
-- TOC entry 5451 (class 2606 OID 505903)
-- Name: countries countries_pk; Type: CONSTRAINT; Schema: public; Owner: geokrety
--

ALTER TABLE ONLY public.countries
    ADD CONSTRAINT countries_pk PRIMARY KEY (id);


--
-- TOC entry 5447 (class 2606 OID 106072)
-- Name: srtm srtm_pkey; Type: CONSTRAINT; Schema: public; Owner: geokrety
--

ALTER TABLE ONLY public.srtm
    ADD CONSTRAINT srtm_pkey PRIMARY KEY (rid);


--
-- TOC entry 5452 (class 1259 OID 505901)
-- Name: geog; Type: INDEX; Schema: public; Owner: geokrety
--

CREATE INDEX geog ON public.countries USING gist (geog);


--
-- TOC entry 5448 (class 1259 OID 106074)
-- Name: srtm_st_convexhull_idx; Type: INDEX; Schema: public; Owner: geokrety
--

CREATE INDEX srtm_st_convexhull_idx ON public.srtm USING gist (public.st_convexhull(rast));


--
-- TOC entry 5449 (class 1259 OID 106075)
-- Name: srtm_st_convexhull_idx1; Type: INDEX; Schema: public; Owner: geokrety
--

CREATE INDEX srtm_st_convexhull_idx1 ON public.srtm USING gist (public.st_convexhull(rast));


-- Completed on 2020-04-15 19:00:57 CEST

--
-- PostgreSQL database dump complete
--

