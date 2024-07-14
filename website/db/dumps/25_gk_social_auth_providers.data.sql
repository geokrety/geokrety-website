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
-- Data for Name: gk_social_auth_providers; Type: TABLE DATA; Schema: geokrety; Owner: -
--

COPY geokrety.gk_social_auth_providers (id, name, created_on_datetime, updated_on_datetime) FROM stdin;
1	Google	2023-04-05 08:56:46.148412+00	2023-04-05 08:56:46.148412+00
2	Facebook	2023-04-05 08:56:46.148412+00	2023-04-05 08:56:46.148412+00
3	Github	2024-05-11 13:00:27.969207+00	2024-05-11 13:00:27.969207+00
\.


--
-- Name: gk_social_auth_providers_id_seq; Type: SEQUENCE SET; Schema: geokrety; Owner: -
--

SELECT pg_catalog.setval('geokrety.gk_social_auth_providers_id_seq', 3, true);


--
-- PostgreSQL database dump complete
--

