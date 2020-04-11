--
-- PostgreSQL database dump
--

-- Dumped from database version 12.2 (Debian 12.2-2.pgdg100+1)
-- Dumped by pg_dump version 12.1

-- Started on 2020-03-31 19:57:42 GMT

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
-- TOC entry 3051 (class 0 OID 21177)
-- Dependencies: 247
-- Data for Name: gk_waypoints_types; Type: TABLE DATA; Schema: geokrety; Owner: geokrety
--

COPY geokrety.gk_waypoints_types (type, cache_type) FROM stdin;
beweglicher Cache	Mobile
BIT Cache	BIT Cache
Cemetery	Cemetery
Drive-In	Drive-In
Drive-In-Cache	Drive-In
Event	Event
Event Cache	Event
Event-Cache	Event
Geocache	Traditional
Geocache|Event Cache	Event
Geocache|Multi-cache	Multicache
Geocache|Mystery Cache	Mystery
Geocache|Traditional Cache	Traditional
Geocache|Unknown Cache	Unknown cache
Geocache|Virtual Cache	Virtual
Geocache|Webcam Cache	Webcam
Guest Book	Guest Book
Inny typ skrzynki	Other
kvíz	Quiz
Letterbox	Letterbox
Mathe-/Physikcache	Math / physics cache
Medical Facility	Medical Facility
Mobilna	Mobile
Moving	Mobile
Moving Cache	Mobile
MP3 (Podcache)	MP3
Multi	Multicache
Multicache	Multicache
neznámá	Unknown cache
normaler Cache	Traditional
Other	Other
Own cache	Own cache
Podcast cache	Podcast cache
Quiz	Quiz
Rätselcache	Mystery
Skrzynka nietypowa	Unusual box
tradiční	Traditional
Traditional	Traditional
Traditional Cache	Traditional
Tradycyjna	Traditional
unbekannter Cachetyp	Unknown cache
Unknown type	Unknown cache
USB (Dead Drop)	USB
Virtual	Virtual
Virtual Cache	Virtual
virtueller Cache	Virtual
Webcam	Webcam
Webcam Cache	Webcam
Webcam-Cache	Webcam
Wirtualna	Virtual
Wydarzenie	Event
\.


-- Completed on 2020-03-31 19:57:42 GMT

--
-- PostgreSQL database dump complete
--

