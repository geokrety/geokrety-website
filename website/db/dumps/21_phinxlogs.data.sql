--
-- PostgreSQL database dump
--

-- Dumped from database version 12.2 (Debian 12.2-2.pgdg100+1)
-- Dumped by pg_dump version 12.2 (Ubuntu 12.2-4)

-- Started on 2020-08-05 13:40:38 CEST

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
-- TOC entry 5670 (class 0 OID 546414)
-- Dependencies: 290
-- Data for Name: phinxlog; Type: TABLE DATA; Schema: geokrety; Owner: geokrety
--

COPY geokrety.phinxlog (version, migration_name, start_time, end_time, breakpoint) FROM stdin;
20200801131052	LabelsLists	2020-08-04 16:12:22+00	2020-08-04 16:12:22+00	f
20200802195759	SessionPersist	2020-08-04 16:12:31+00	2020-08-04 16:12:31+00	f
\.


-- Completed on 2020-08-05 13:40:38 CEST

--
-- PostgreSQL database dump complete
--

