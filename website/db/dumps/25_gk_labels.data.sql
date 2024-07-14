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
-- Data for Name: gk_labels; Type: TABLE DATA; Schema: geokrety; Owner: -
--

COPY geokrety.gk_labels (id, template, title, author, created_on_datetime, updated_on_datetime) FROM stdin;
1	default	Default	GK Team	2023-04-05 08:56:46.101138+00	2023-04-05 08:56:46.101138+00
2	sansanchoz1	Key chain HR	SanSanchoz	2023-04-05 08:56:46.101138+00	2023-04-05 08:56:46.101138+00
3	sansanchoz2	Key chain VR	SanSanchoz	2023-04-05 08:56:46.101138+00	2023-04-05 08:56:46.101138+00
4	schrottie	Modern Schrottie	Schrottie	2023-04-05 08:56:46.101138+00	2023-04-05 08:56:46.101138+00
5	stamp.black	Stamp Black	GK Team	2023-04-05 08:56:46.101138+00	2023-04-05 08:56:46.101138+00
6	stamp.blue	Stamp Blue	GK Team	2023-04-05 08:56:46.101138+00	2023-04-05 08:56:46.101138+00
7	stamp.green	Stamp Green	GK Team	2023-04-05 08:56:46.101138+00	2023-04-05 08:56:46.101138+00
8	stamp.orange	Stamp Orange	GK Team	2023-04-05 08:56:46.101138+00	2023-04-05 08:56:46.101138+00
9	stamp.purple	Stamp Purple	GK Team	2023-04-05 08:56:46.101138+00	2023-04-05 08:56:46.101138+00
10	stamp.red	Stamp Red	GK Team	2023-04-05 08:56:46.101138+00	2023-04-05 08:56:46.101138+00
11	stamp.white	Stamp White	GK Team	2023-04-05 08:56:46.101138+00	2023-04-05 08:56:46.101138+00
12	stamp.yellow	Stamp Yellow	GK Team	2023-04-05 08:56:46.101138+00	2023-04-05 08:56:46.101138+00
13	middleclassic	Middle classic	Filips	2023-04-05 08:56:46.101138+00	2023-04-05 08:56:46.101138+00
14	wallson1	Modern Wallson 1	Wallson	2023-04-05 08:56:46.101138+00	2023-04-05 08:56:46.101138+00
15	wallson2	Modern Wallson 2	Wallson	2023-04-05 08:56:46.101138+00	2023-04-05 08:56:46.101138+00
\.


--
-- Name: gk_labels_id_seq; Type: SEQUENCE SET; Schema: geokrety; Owner: -
--

SELECT pg_catalog.setval('geokrety.gk_labels_id_seq', 15, true);


--
-- PostgreSQL database dump complete
--

