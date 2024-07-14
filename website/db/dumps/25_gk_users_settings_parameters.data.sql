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
-- Data for Name: gk_users_settings_parameters; Type: TABLE DATA; Schema: geokrety; Owner: -
--

COPY geokrety.gk_users_settings_parameters (name, type, "default", description, created_on_datetime, updated_on_datetime) FROM stdin;
TRACKING_OPT_OUT	bool	false	Opt-out from site usage analytics	2023-04-05 09:22:25.452359+00	2023-04-05 09:22:25.452359+00
DISTANCE_UNIT	enum:metric|imperial	metric	Display distances unit (kilometers|miles)	2023-08-12 17:31:16.339082+00	2023-08-12 17:31:16.339082+00
DISPLAY_ABSOLUTE_DATE	bool	false	Display dates as absolute	2023-09-27 17:42:43.424446+00	2023-09-27 17:42:43.424446+00
\.


--
-- PostgreSQL database dump complete
--

