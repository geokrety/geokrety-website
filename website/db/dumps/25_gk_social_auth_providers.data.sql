TRUNCATE geokrety.gk_social_auth_providers CASCADE;
--
-- PostgreSQL database dump
--

\restrict g7KkbibTWWPB9eBoqeEaSJ6Brb44wfY1sSIMddrgdEXN2jkftjQFlRkxo3ey6fN

-- Dumped from database version 16.13 (Ubuntu 16.13-1.pgdg24.04+1)
-- Dumped by pg_dump version 18.3 (Ubuntu 18.3-1.pgdg22.04+1)

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
-- Data for Name: gk_social_auth_providers; Type: TABLE DATA; Schema: geokrety; Owner: -
--

SET SESSION AUTHORIZATION DEFAULT;

ALTER TABLE geokrety.gk_social_auth_providers DISABLE TRIGGER ALL;

COPY geokrety.gk_social_auth_providers (id, name, created_on_datetime, updated_on_datetime) FROM stdin;
1	Google	2023-04-05 08:56:46.148412+00	2023-04-05 08:56:46.148412+00
2	Facebook	2023-04-05 08:56:46.148412+00	2023-04-05 08:56:46.148412+00
3	Github	2024-05-11 13:00:27.969207+00	2024-05-11 13:00:27.969207+00
\.


ALTER TABLE geokrety.gk_social_auth_providers ENABLE TRIGGER ALL;

--
-- Name: gk_social_auth_providers_id_seq; Type: SEQUENCE SET; Schema: geokrety; Owner: -
--

SELECT pg_catalog.setval('geokrety.gk_social_auth_providers_id_seq', 3, true);


--
-- PostgreSQL database dump complete
--

\unrestrict g7KkbibTWWPB9eBoqeEaSJ6Brb44wfY1sSIMddrgdEXN2jkftjQFlRkxo3ey6fN

