--
-- PostgreSQL database dump
--

-- Dumped from database version 12.2 (Debian 12.2-2.pgdg100+1)
-- Dumped by pg_dump version 12.2 (Ubuntu 12.2-2.pgdg19.10+1)

-- Started on 2020-04-11 13:47:05 CEST

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
-- TOC entry 231 (class 1259 OID 17989)
-- Name: countries; Type: TABLE; Schema: public; Owner: geokrety
--

CREATE TABLE public.countries (
    id integer NOT NULL,
    iso_a2 character varying(2) NOT NULL,
    geom public.geometry(Geometry,4326) NOT NULL
);


ALTER TABLE public.countries OWNER TO geokrety;

--
-- TOC entry 232 (class 1259 OID 17995)
-- Name: gk_countries_id_seq; Type: SEQUENCE; Schema: public; Owner: geokrety
--

CREATE SEQUENCE public.gk_countries_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.gk_countries_id_seq OWNER TO geokrety;

--
-- TOC entry 5555 (class 0 OID 0)
-- Dependencies: 232
-- Name: gk_countries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: geokrety
--

ALTER SEQUENCE public.gk_countries_id_seq OWNED BY public.countries.id;


--
-- TOC entry 233 (class 1259 OID 17997)
-- Name: srtm; Type: TABLE; Schema: public; Owner: geokrety
--

CREATE TABLE public.srtm (
    rid integer NOT NULL,
    rast public.raster,
    filename text
);


ALTER TABLE public.srtm OWNER TO geokrety;

--
-- TOC entry 234 (class 1259 OID 18003)
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
-- TOC entry 235 (class 1259 OID 18007)
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
-- TOC entry 5556 (class 0 OID 0)
-- Dependencies: 235
-- Name: srtm_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: geokrety
--

ALTER SEQUENCE public.srtm_rid_seq OWNED BY public.srtm.rid;


--
-- TOC entry 5407 (class 2604 OID 18009)
-- Name: countries id; Type: DEFAULT; Schema: public; Owner: geokrety
--

ALTER TABLE ONLY public.countries ALTER COLUMN id SET DEFAULT nextval('public.gk_countries_id_seq'::regclass);


--
-- TOC entry 5408 (class 2604 OID 18010)
-- Name: srtm rid; Type: DEFAULT; Schema: public; Owner: geokrety
--

ALTER TABLE ONLY public.srtm ALTER COLUMN rid SET DEFAULT nextval('public.srtm_rid_seq'::regclass);


--
-- TOC entry 5411 (class 2606 OID 18012)
-- Name: srtm srtm_pkey; Type: CONSTRAINT; Schema: public; Owner: geokrety
--

ALTER TABLE ONLY public.srtm
    ADD CONSTRAINT srtm_pkey PRIMARY KEY (rid);


--
-- TOC entry 5409 (class 1259 OID 18013)
-- Name: gk_countries_iso_a2; Type: INDEX; Schema: public; Owner: geokrety
--

CREATE INDEX gk_countries_iso_a2 ON public.countries USING btree (iso_a2);


--
-- TOC entry 5412 (class 1259 OID 18014)
-- Name: srtm_st_convexhull_idx; Type: INDEX; Schema: public; Owner: geokrety
--

CREATE INDEX srtm_st_convexhull_idx ON public.srtm USING gist (public.st_convexhull(rast));


--
-- TOC entry 5413 (class 1259 OID 18015)
-- Name: srtm_st_convexhull_idx1; Type: INDEX; Schema: public; Owner: geokrety
--

CREATE INDEX srtm_st_convexhull_idx1 ON public.srtm USING gist (public.st_convexhull(rast));


-- Completed on 2020-04-11 13:47:07 CEST

--
-- PostgreSQL database dump complete
--

