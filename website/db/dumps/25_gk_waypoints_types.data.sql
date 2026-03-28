TRUNCATE geokrety.gk_waypoints_types CASCADE;
--
-- PostgreSQL database dump
--

\restrict 6LtFHhbqYEfWRDsOpcYTop5gcPXVmz3NA7Llh7XDd08H5u6bHA6aWpSyMDm38WS

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
-- Data for Name: gk_waypoints_types; Type: TABLE DATA; Schema: geokrety; Owner: -
--

SET SESSION AUTHORIZATION DEFAULT;

ALTER TABLE geokrety.gk_waypoints_types DISABLE TRIGGER ALL;

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


ALTER TABLE geokrety.gk_waypoints_types ENABLE TRIGGER ALL;

--
-- PostgreSQL database dump complete
--

\unrestrict 6LtFHhbqYEfWRDsOpcYTop5gcPXVmz3NA7Llh7XDd08H5u6bHA6aWpSyMDm38WS

