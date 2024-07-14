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
-- Data for Name: gk_site_settings_parameters; Type: TABLE DATA; Schema: geokrety; Owner: -
--

COPY geokrety.gk_site_settings_parameters (name, type, "default", description, created_on_datetime, updated_on_datetime) FROM stdin;
ADMIN_EMAIL_BCC_ENABLED	bool	false	When enabled, admin will be set as bcc for all mails	2024-05-11 13:00:27.896295+00	2024-05-11 13:00:27.896295+00
\.


--
-- PostgreSQL database dump complete
--

